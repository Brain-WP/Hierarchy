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

use Brain\Hierarchy\PostTemplates;
use Brain\Hierarchy\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class PostTemplatesTest extends TestCase
{
    /**
     * @test
     */
    public function testEmptyPostReturnEmptyString(): void
    {
        $postTemplates = new PostTemplates();

        $post = \Mockery::mock(\WP_Post::class);
        $post->ID = '';
        $post->post_type = '';

        static::assertSame('', $postTemplates->findFor($post));
    }

    /**
     * @test
     */
    public function testPostWithoutTemplateReturnEmptyString(): void
    {
        $postTemplates = new PostTemplates();

        $post = \Mockery::mock(\WP_Post::class);
        $post->ID = 123;
        $post->post_type = 'event';

        Functions\expect('get_page_template_slug')
            ->once()
            ->with($post)
            ->andReturn(false);

        static::assertSame('', $postTemplates->findFor($post));
    }

    /**
     * @test
     */
    public function testPostWithNonexistentTemplateReturnEmptyString(): void
    {
        $postTemplates = new PostTemplates();

        $post = \Mockery::mock(\WP_Post::class);
        $post->ID = 123;
        $post->post_type = 'event';

        Functions\expect('get_page_template_slug')
            ->once()
            ->with($post)
            ->andReturn('foo.php');

        Functions\expect('validate_file')
            ->once()
            ->with('foo.php')
            ->andReturn(0);

        $theme = \Mockery::mock(\WP_Theme::class);
        $theme->expects('get_page_templates')
            ->with(null, 'event')
            ->andReturn(['bar.php' => 'Bar']);

        Functions\expect('wp_get_theme')
            ->once()
            ->andReturn($theme);

        static::assertSame('', $postTemplates->findFor($post));
    }

    /**
     * @test
     */
    public function testPostWithValidTemplateReturnItsName(): void
    {
        $postTemplates = new PostTemplates();

        $post = \Mockery::mock(\WP_Post::class);
        $post->ID = 123;
        $post->post_type = 'event';

        Functions\expect('get_page_template_slug')
            ->once()
            ->with($post)
            ->andReturn('foo.php');

        Functions\expect('validate_file')
            ->atLeast()
            ->once()
            ->with('foo.php')
            ->andReturn(0);

        $theme = \Mockery::mock(\WP_Theme::class);
        $theme->expects('get_page_templates')
            ->with(null, 'event')
            ->andReturn(['foo.php' => 'Foo', 'bar.php' => 'Bar']);

        Functions\expect('wp_get_theme')
            ->once()
            ->andReturn($theme);

        static::assertSame('foo', $postTemplates->findFor($post));
    }

    /**
     * @test
     */
    public function testPostWithValidTemplateReturnItsNameAndDir(): void
    {
        $postTemplates = new PostTemplates();

        $post = \Mockery::mock(\WP_Post::class);
        $post->ID = 123;
        $post->post_type = 'event';

        Functions\expect('get_page_template_slug')
            ->once()
            ->with($post)
            ->andReturn('path/foo.php');

        Functions\expect('validate_file')
            ->atLeast()
            ->once()
            ->with('path/foo.php')
            ->andReturn(0);

        $theme = \Mockery::mock(\WP_Theme::class);
        $theme->expects('get_page_templates')
            ->with(null, 'event')
            ->andReturn(['path/foo.php' => 'Foo', 'bar.php' => 'Bar']);

        Functions\expect('wp_get_theme')
            ->once()
            ->andReturn($theme);

        static::assertSame('path/foo', $postTemplates->findFor($post));
    }

    /**
     * @test
     */
    public function testTemplatesAreReadOnceFromTheme(): void
    {
        $postTemplates = new PostTemplates();

        $post = \Mockery::mock(\WP_Post::class);
        $post->ID = 123;
        $post->post_type = 'event';

        Functions\expect('get_page_template_slug')
            ->atLeast()
            ->once()
            ->with($post)
            ->andReturn('path/foo.php');

        Functions\expect('validate_file')
            ->atLeast()
            ->once()
            ->with('path/foo.php')
            ->andReturn(0);

        $theme = \Mockery::mock(\WP_Theme::class);
        $theme->expects('get_page_templates')
            ->with(null, 'event')
            ->andReturn(['path/foo.php' => 'Foo', 'bar.php' => 'Bar']);

        Functions\expect('wp_get_theme')
            ->once()
            ->andReturn($theme);

        $postTemplates->findFor($post);
        $postTemplates->findFor($post);
        $postTemplates->findFor($post);
        $postTemplates->findFor($post);

        static::assertSame('path/foo', $postTemplates->findFor($post));
    }
}
