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

namespace Brain\Hierarchy\Tests\Functional;

use Brain\Monkey\Functions;
use Brain\Hierarchy\Finder\ByFolders;
use Brain\Hierarchy\Finder\Localized;
use Brain\Hierarchy\Finder\SymfonyFinderAdapter;
use Brain\Hierarchy\QueryTemplate;
use Brain\Hierarchy\Tests\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class QueryTemplateTest extends TestCase
{
    /**
     * @test
     */
    public function testLoadPageCustom(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'a-page';
        $post->post_type = 'page';

        $theme = \Mockery::mock('\WP_Theme');
        $theme
            ->expects('get_page_templates')
            ->andReturn(['page-templates/page-custom.php' => 'Custom']);

        Functions\expect('wp_get_theme')->andReturn($theme);

        Functions\expect('get_page_template_slug')
            ->with($post)
            ->andReturn('page-templates/page-custom.php');
        Functions\expect('validate_file')
            ->with('page-templates/page-custom.php')
            ->andReturn(0);
        Functions\expect('wp_normalize_path')
            ->with('page-templates/page-custom.php')
            ->andReturn('page-templates/page-custom.php');

        $wpQuery = new \WP_Query(['is_page' => true], $post, ['pagename' => 'a-page']);

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH') . '/files'];
        $loader = new QueryTemplate(new ByFolders($folders, 'twig'));

        static::assertSame('page custom', $loader->loadTemplate($wpQuery));
    }

    /**
     * @test
     */
    public function testLoadPageSingular(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_type = 'page';
        $post->post_name = 'foo';

        Functions\expect('get_page_template_slug')->with($post)->andReturn('');

        $wpQuery = new \WP_Query(['is_page' => true, 'is_singular' => true], $post);

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH') . '/files'];
        $loader = new QueryTemplate(new ByFolders($folders, 'twig'));

        static::assertSame('singular', $loader->loadTemplate($wpQuery));
    }

    /**
     * @test
     */
    public function testLocalizedTaxonomy(): void
    {
        Functions\when('get_stylesheet_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH');
        });

        Functions\when('get_locale')->alias(static function (): string {
            return 'it_IT';
        });

        $wpQuery = new \WP_Query(
            [
                'is_tax' => true,
                'is_archive' => true,
            ],
            (object)['slug' => 'bar', 'taxonomy' => 'foo']
        );

        $finder = new Finder();
        $finder->in([get_stylesheet_directory() . '/files'])
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs(true)
            ->followLinks();

        $loader = new QueryTemplate(new Localized(new SymfonyFinderAdapter($finder)));

        static::assertSame('foo bar', $loader->loadTemplate($wpQuery));
    }

    /**
     * @test
     */
    public function testFallbackToArchive(): void
    {
        Functions\when('get_stylesheet_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH') . '/files/it_IT';
        });

        Functions\when('get_template_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH') . '/files/it_IT';
        });

        $wpQuery = new \WP_Query(
            [
                'is_tax' => true,
                'is_archive' => true,
            ],
            (object)['slug' => 'bar', 'taxonomy' => 'foo']
        );

        $loader = new QueryTemplate();

        static::assertSame('archive', $loader->loadTemplate($wpQuery));
    }

    /**
     * @test
     */
    public function testFallbackToIndex(): void
    {
        Functions\when('get_stylesheet_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH') . '/files';
        });

        Functions\when('get_template_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH') . '/files';
        });

        $wpTaxQuery = new \WP_Query(
            [
                'is_tax' => true,
                'is_archive' => true,
            ],
            (object)['slug' => 'bar', 'taxonomy' => 'foo']
        );

        $wpSearchQuery = new \WP_Query(['is_search' => true]);

        $loader = new QueryTemplate();

        static::assertSame('index', $loader->loadTemplate($wpTaxQuery));
        static::assertSame('index', $loader->loadTemplate($wpSearchQuery));
    }
}
