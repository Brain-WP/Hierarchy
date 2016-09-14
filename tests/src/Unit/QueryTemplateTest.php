<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Unit;

use Andrew\Proxy;
use Brain\Monkey\WP\Filters;
use Brain\Monkey\Functions;
use Brain\Hierarchy\QueryTemplate;
use Brain\Hierarchy\Tests\TestCase;
use Brain\Hierarchy\Finder\TemplateFinderInterface;
use Brain\Hierarchy\Loader\TemplateLoaderInterface;
use Brain\Hierarchy\Finder\FoldersTemplateFinder;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Hierarchy
 */
class QueryTemplateTest extends TestCase
{
    public function testFindEmptyWhenNoLeaves()
    {
        $finder = Mockery::mock(TemplateFinderInterface::class);
        $loader = new QueryTemplate($finder);
        $found = $loader->findTemplate();

        assertSame('', $found);
    }

    public function testFindNoFilters()
    {
        $wpQuery = new \WP_Query();
        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/index.php';
        $finder = Mockery::mock(TemplateFinderInterface::class);
        $finder->shouldReceive('findFirst')->once()->with(['index'], 'index')->andReturn($template);

        $loader = new QueryTemplate($finder);

        assertSame($template, $loader->findTemplate($wpQuery, false));
    }

    public function testFindFilters()
    {
        Filters::expectApplied('index_template')->once()->andReturn('foo.php');

        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/index.php';
        $wpQuery = new \WP_Query();
        $finder = Mockery::mock(TemplateFinderInterface::class);
        $finder->shouldReceive('findFirst')->once()->with(['index'], 'index')->andReturn($template);

        $loader = new QueryTemplate($finder);

        assertSame('foo.php', $loader->findTemplate($wpQuery, true));
    }

    public function testLoadNoFilters()
    {
        $wpQuery = new \WP_Query();
        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/index.php';
        $finder = Mockery::mock(TemplateFinderInterface::class);
        $finder->shouldReceive('findFirst')->once()->with(['index'], 'index')->andReturn($template);

        $loader = new QueryTemplate($finder);

        assertSame('index', $loader->loadTemplate($wpQuery, false));
    }

    public function testLoadFilters()
    {
        $wpQuery = new \WP_Query();
        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/another.php';

        Filters::expectApplied('template_include')->once()->andReturn($template);

        $finder = Mockery::mock(TemplateFinderInterface::class);
        $finder->shouldReceive('findFirst')->once()->with(['index'], 'index')->andReturn('foo');

        $loader = new QueryTemplate($finder);

        assertSame('another', $loader->loadTemplate($wpQuery, true));
    }

    public function testLoadTemplateFoundFalse()
    {
        $wpQuery = new \WP_Query();

        $finder = Mockery::mock(TemplateFinderInterface::class);
        $finder->shouldReceive('findFirst')->once()->with(['index'], 'index')->andReturn('foo');

        $found = true;

        $loader = new QueryTemplate($finder);
        $loaded = $loader->loadTemplate($wpQuery, false, $found);

        assertFalse($found);
        assertSame('', $loaded);
    }

    public function testLoadTemplateFoundTrue()
    {
        $wpQuery = new \WP_Query();

        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/page.php';

        $finder = Mockery::mock(TemplateFinderInterface::class);
        $finder->shouldReceive('findFirst')->once()->andReturn($template);

        $found = false;

        $loader = new QueryTemplate($finder);
        $loaded = $loader->loadTemplate($wpQuery, false, $found);

        assertTrue($found);
        assertSame('page', $loaded);
    }

    public function testApplyFilters()
    {
        $wpQuery = Mockery::mock('WP_Query');
        $wpQuery->shouldReceive('is_main_query')->withNoArgs()->andReturn(true);

        global $wp_query, $wp_the_query;
        $wp_query = $wp_the_query = $wpQuery;
        $customQuery = new \WP_Query();

        // during filter, globals `$wp_query` and `$wp_the_query` are equal to custom query
        Filters::expectApplied('test_filter')
            ->once()
            ->with('foo')
            ->andReturnUsing(
                function () use ($customQuery) {
                    assertSame($GLOBALS['wp_query'], $customQuery);
                    assertSame($GLOBALS['wp_the_query'], $customQuery);

                    return 'bar!';
                }
            );

        $finder = \Mockery::mock(TemplateFinderInterface::class);
        $queryTemplate = new Proxy(new QueryTemplate($finder));
        $applied = $queryTemplate->applyFilter('test_filter', 'foo', $customQuery);

        // after filter, globals `$wp_query` and `$wp_the_query` are restored

        assertSame($GLOBALS['wp_query'], $wpQuery);
        assertSame($GLOBALS['wp_the_query'], $wpQuery);
        assertSame('bar!', $applied);

        unset($wp_query, $wp_the_query);
    }

    public function testMainQueryTemplateAllowedTrue()
    {
        Functions::when('is_robots')->justReturn(false);
        Functions::when('is_feed')->justReturn(false);
        Functions::when('is_trackback')->justReturn(false);
        Functions::when('is_embed')->justReturn(false);

        assertTrue(QueryTemplate::mainQueryTemplateAllowed());
    }

    public function testMainQueryTemplateAllowedFalseIsRobots()
    {
        Functions::when('is_robots')->justReturn(true);
        Functions::when('is_feed')->justReturn(false);
        Functions::when('is_trackback')->justReturn(false);
        Functions::when('is_embed')->justReturn(false);

        assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }

    public function testMainQueryTemplateAllowedFalseIsFeed()
    {
        Functions::when('is_robots')->justReturn(false);
        Functions::when('is_feed')->justReturn(true);
        Functions::when('is_trackback')->justReturn(false);
        Functions::when('is_embed')->justReturn(false);

        assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }

    public function testMainQueryTemplateAllowedFalseIsTrackback()
    {
        Functions::when('is_robots')->justReturn(false);
        Functions::when('is_feed')->justReturn(false);
        Functions::when('is_trackback')->justReturn(true);
        Functions::when('is_embed')->justReturn(false);

        assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }

    public function testMainQueryTemplateAllowedFalseIsEmbed()
    {
        Functions::when('is_robots')->justReturn(false);
        Functions::when('is_feed')->justReturn(false);
        Functions::when('is_trackback')->justReturn(false);
        Functions::when('is_embed')->justReturn(true);

        assertFalse(QueryTemplate::mainQueryTemplateAllowed());
    }

    public function testInstanceWithLoader()
    {
        Functions::when('get_stylesheet_directory')->justReturn();
        Functions::when('get_template_directory')->justReturn();
        
        $loader = Mockery::mock(TemplateLoaderInterface::class);
        $instance = QueryTemplate::instanceWithLoader($loader);
        $proxy = new Proxy($instance);

        assertInstanceOf(QueryTemplate::class, $instance);
        assertSame($loader, $proxy->loader);
    }

    public function instanceWithFolders()
    {
        $folders = [__DIR__];
        $loader = Mockery::mock(TemplateLoaderInterface::class);
        $instance = QueryTemplate::instanceWithFolders($folders, $loader);
        $proxy = new Proxy($instance);

        assertInstanceOf(QueryTemplate::class, $instance);
        assertInstanceOf(FoldersTemplateFinder::class, $proxy->finder);
        assertSame($loader, $proxy->loader);
    }
}
