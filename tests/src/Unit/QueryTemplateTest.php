<?php

/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Brain\Hierarchy\Tests\Unit;

use Brain\Hierarchy\Branch\Branch;
use Brain\Hierarchy\Finder\FindFirstTrait;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Brain\Hierarchy\QueryTemplate;
use Brain\Hierarchy\Tests\TestCase;
use Brain\Hierarchy\Finder\TemplateFinder;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class QueryTemplateTest extends TestCase
{
    /**
     * @test
     */
    public function testFindNoFilters(): void
    {
        $wpQuery = new \WP_Query();
        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/index.php';
        $finder = \Mockery::mock(TemplateFinder::class);
        $finder->expects('findFirst')->with(['index'], 'index')->andReturn($template);

        $loader = new QueryTemplate($finder);

        static::assertSame($template, $loader->findTemplate($wpQuery, false));
    }

    /**
     * @test
     */
    public function testFindFilters(): void
    {
        Filters\expectApplied('index_template')->once()->andReturn('foo.php');

        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/index.php';
        $wpQuery = new \WP_Query();
        $finder = \Mockery::mock(TemplateFinder::class);
        $finder->expects('findFirst')->with(['index'], 'index')->andReturn($template);

        $loader = new QueryTemplate($finder);

        static::assertSame('foo.php', $loader->findTemplate($wpQuery, true));
    }

    /**
     * @test
     */
    public function testLoadNoFilters(): void
    {
        $wpQuery = new \WP_Query();
        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/index.php';
        $finder = \Mockery::mock(TemplateFinder::class);
        $finder->expects('findFirst')->with(['index'], 'index')->andReturn($template);

        $loader = new QueryTemplate($finder);

        static::assertSame('index', $loader->loadTemplate($wpQuery, false));
    }

    /**
     * @test
     */
    public function testLoadFilters(): void
    {
        $wpQuery = new \WP_Query();
        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/another.php';

        Filters\expectApplied('template_include')->once()->andReturn($template);

        $finder = \Mockery::mock(TemplateFinder::class);
        $finder->expects('findFirst')->with(['index'], 'index')->andReturn('foo');

        $loader = new QueryTemplate($finder);

        static::assertSame('another', $loader->loadTemplate($wpQuery, true));
    }

    /**
     * @test
     */
    public function testLoadTemplateFoundFalse(): void
    {
        $wpQuery = new \WP_Query();

        $finder = \Mockery::mock(TemplateFinder::class);
        $finder->expects('findFirst')->with(['index'], 'index')->andReturn('foo');

        $found = true;

        $loader = new QueryTemplate($finder);
        $loaded = $loader->loadTemplate($wpQuery, false, $found);

        static::assertFalse($found);
        static::assertSame('', $loaded);
    }

    /**
     * @test
     */
    public function testLoadTemplateFoundTrue(): void
    {
        $wpQuery = new \WP_Query();

        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/page.php';

        $finder = \Mockery::mock(TemplateFinder::class);
        $finder->expects('findFirst')->andReturn($template);

        $found = false;

        $loader = new QueryTemplate($finder);
        $loaded = $loader->loadTemplate($wpQuery, false, $found);

        static::assertTrue($found);
        static::assertSame('page', $loaded);
    }

    /**
     * @test
     */
    public function testApplyFilters(): void
    {
        $mainQuery = \Mockery::mock('WP_Query');

        global $wp_query, $wp_the_query;
        $wp_query = $mainQuery;
        $wp_the_query = $mainQuery;
        $customQuery = new \WP_Query();

        Filters\expectApplied('foo_template')
            ->once()
            ->with(...['found!', 'foo', ['foo', 'bar']])
            ->andReturnUsing(
                static function () use ($customQuery): string {
                    // during filter, globals `$wp_query` and `$wp_the_query` are equal to custom
                    global $wp_query, $wp_the_query;
                    static::assertSame($wp_query, $customQuery);
                    static::assertSame($wp_the_query, $customQuery);

                    return 'filtered!';
                }
            );

        $branch = new \Brain\Hierarchy\Tests\Stubs\BranchStubFoo();
        Filters\expectApplied('brain.hierarchy.branches')->once()->andReturn([$branch]);

        $finder = new class implements TemplateFinder
        {
            use FindFirstTrait;

            public function find(string $template, string $type): string
            {
                return 'found!';
            }
        };

        $queryTemplate = new QueryTemplate($finder);
        $found = $queryTemplate->findTemplate($customQuery);

        // after filter, globals `$wp_query` and `$wp_the_query` are restored
        static::assertSame($wp_query, $mainQuery);
        static::assertSame($wp_query, $mainQuery);

        static::assertSame('filtered!', $found);

        unset($wp_query, $wp_the_query);
    }

    /**
     * @test
     */
    public function testFindTemplateWhen404(): void
    {
        $wpQuery = new \WP_Query([
            'is_404' => true,
        ]);

        $finder = \Mockery::mock(TemplateFinder::class);
        $finder->expects('findFirst')->andReturn('foo');

        $loader = new QueryTemplate($finder);
        $loaded = $loader->findTemplate($wpQuery);

        static::assertSame('foo', $loaded);
    }

    /**
     * @test
     */
    public function testMainQueryTemplateAllowedTrue(): void
    {
        Functions\when('is_robots')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);
        Functions\when('is_trackback')->justReturn(false);
        Functions\when('is_embed')->justReturn(false);
        Functions\when('is_favicon')->justReturn(false);

        static::assertTrue(QueryTemplate::mainQueryTemplateAllowed());
    }

    /**
     * @test
     */
    public function testMainQueryTemplateAllowedFalseIsRobots(): void
    {
        Functions\when('is_robots')->justReturn(true);
        Functions\when('is_feed')->justReturn(false);
        Functions\when('is_trackback')->justReturn(false);
        Functions\when('is_embed')->justReturn(false);
        Functions\when('is_favicon')->justReturn(false);

        static::assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }

    /**
     * @test
     */
    public function testMainQueryTemplateAllowedFalseIsFeed(): void
    {
        Functions\when('is_robots')->justReturn(false);
        Functions\when('is_feed')->justReturn(true);
        Functions\when('is_trackback')->justReturn(false);
        Functions\when('is_embed')->justReturn(false);
        Functions\when('is_favicon')->justReturn(false);

        static::assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }

    /**
     * @test
     */
    public function testMainQueryTemplateAllowedFalseIsTrackback(): void
    {
        Functions\when('is_robots')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);
        Functions\when('is_trackback')->justReturn(true);
        Functions\when('is_embed')->justReturn(false);
        Functions\when('is_favicon')->justReturn(false);

        static::assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }

    /**
     * @test
     */
    public function testMainQueryTemplateAllowedFalseIsEmbed(): void
    {
        Functions\when('is_robots')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);
        Functions\when('is_trackback')->justReturn(false);
        Functions\when('is_embed')->justReturn(true);
        Functions\when('is_favicon')->justReturn(false);

        static::assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }

    /**
     * @test
     */
    public function testMainQueryTemplateAllowedFalseIsFavicon(): void
    {
        Functions\when('is_robots')->justReturn(false);
        Functions\when('is_feed')->justReturn(false);
        Functions\when('is_trackback')->justReturn(false);
        Functions\when('is_embed')->justReturn(false);
        Functions\when('is_favicon')->justReturn(true);

        static::assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }
}
