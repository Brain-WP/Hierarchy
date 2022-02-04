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

namespace Brain\Hierarchy\Tests\Unit\Branch;

use Brain\Hierarchy\PostTemplates;
use Brain\Monkey\Functions;
use Brain\Hierarchy\Branch\BranchPage;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class BranchPageTest extends TestCase
{
    /**
     * @test
     */
    public function testLeavesNoPageNoPagename(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 0;
        $post->post_name = '';
        $post->post_type = '';

        $query = new \WP_Query([], $post, ['pagename' => '']);

        $branch = new BranchPage();

        static::assertSame(['page'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeavesNoPage(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 0;
        $post->post_name = '';
        $post->post_type = '';

        $query = new \WP_Query([], $post, ['pagename' => 'foo']);
        Functions\expect('get_page_template_slug')->with($post)->andReturn('');

        $branch = new BranchPage();

        static::assertSame(['page-foo', 'page'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeavesPage(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'foo';
        $post->post_type = 'page';

        $postTemplates = \Mockery::mock(PostTemplates::class);
        $postTemplates
            ->expects('findFor')
            ->with($post)
            ->andReturn('');

        $query = new \WP_Query([], $post, ['pagename' => '']);

        $branch = new BranchPage($postTemplates);
        static::assertSame(['page-foo', 'page-1', 'page'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeavesPagePagename(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'foo';
        $post->post_type = 'page';

        $postTemplates = \Mockery::mock(PostTemplates::class);
        $postTemplates
            ->expects('findFor')
            ->with($post)
            ->andReturn('');

        $query = new \WP_Query([], $post, ['pagename' => 'bar']);

        $branch = new BranchPage($postTemplates);

        static::assertSame(['page-bar', 'page-1', 'page'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeavesPagePagenameTemplate(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'foo';
        $post->post_type = 'page';

        $postTemplates = \Mockery::mock(PostTemplates::class);
        $postTemplates
            ->expects('findFor')
            ->with($post)
            ->andReturn('page-meh');

        $query = new \WP_Query([], $post, ['pagename' => 'bar']);

        $branch = new BranchPage($postTemplates);

        static::assertSame(['page-meh', 'page-bar', 'page-1', 'page'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeavesPagePagenameTemplateFolder(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'foo';
        $post->post_type = 'page';

        $query = new \WP_Query([], $post, ['pagename' => 'bar']);

        $postTemplates = \Mockery::mock(PostTemplates::class);
        $postTemplates
            ->expects('findFor')
            ->with($post)
            ->andReturn('page-templates/page-meh');

        $branch = new BranchPage($postTemplates);

        $expected = ['page-templates/page-meh', 'page-bar', 'page-1', 'page'];

        static::assertSame($expected, $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testPageNameReturnTemplateIfNoPagename(): void
    {
        Functions\when('sanitize_title')->alias('strtolower');

        $post = \Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = '';
        $post->post_title = 'foo';
        $post->post_type = 'page';

        $query = new \WP_Query(['is_preview' => true], $post, ['page_id' => 1, 'pagename' => '']);

        $postTemplates = \Mockery::mock(PostTemplates::class);
        $postTemplates
            ->expects('findFor')
            ->with($post)
            ->andReturn('page-foo');

        $branch = new BranchPage($postTemplates);

        $expected = ['page-foo', 'page-1', 'page'];

        static::assertSame($expected, $branch->leaves($query));
    }
}
