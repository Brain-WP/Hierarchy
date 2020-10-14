<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Unit\Branch;

use Brain\Hierarchy\PostTemplates;
use Brain\Monkey\Functions;
use Brain\Hierarchy\Branch\BranchPage;
use Brain\Hierarchy\Tests\TestCase;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class BranchPageTest extends TestCase
{

    public function testLeavesNoPageNoPagename()
    {
        $post = Mockery::mock('\WP_Post');
        $post->ID = 0;
        $post->post_name = '';
        $post->post_type = '';

        $query = new \WP_Query([], $post, ['pagename' => '']);

        $branch = new BranchPage();

        static::assertSame(['page'], $branch->leaves($query));
    }

    public function testLeavesNoPage()
    {
        $post = Mockery::mock('\WP_Post');
        $post->ID = 0;
        $post->post_name = '';
        $post->post_type = '';

        $query = new \WP_Query([], $post, ['pagename' => 'foo']);
        Functions\expect('get_page_template_slug')->with($post)->andReturn('');

        $branch = new BranchPage();

        static::assertSame(['page-foo', 'page'], $branch->leaves($query));
    }

    public function testLeavesPage()
    {
        $post = Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'foo';
        $post->post_type = 'page';

        $postTemplates = Mockery::mock(PostTemplates::class);
        $postTemplates
            ->shouldReceive('findFor')
            ->once()
            ->with($post)
            ->andReturn('');

        $query = new \WP_Query([], $post, ['pagename' => '']);

        $branch = new BranchPage($postTemplates);
        static::assertSame(['page-foo', 'page-1', 'page'], $branch->leaves($query));
    }

    public function testLeavesPagePagename()
    {
        $post = Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'foo';
        $post->post_type = 'page';

        $postTemplates = Mockery::mock(PostTemplates::class);
        $postTemplates
            ->shouldReceive('findFor')
            ->once()
            ->with($post)
            ->andReturn('');

        $query = new \WP_Query([], $post, ['pagename' => 'bar']);

        $branch = new BranchPage($postTemplates);

        static::assertSame(['page-bar', 'page-1', 'page'], $branch->leaves($query));
    }

    public function testLeavesPagePagenameTemplate()
    {
        $post = Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'foo';
        $post->post_type = 'page';

        $postTemplates = Mockery::mock(PostTemplates::class);
        $postTemplates
            ->shouldReceive('findFor')
            ->once()
            ->with($post)
            ->andReturn('page-meh');

        $query = new \WP_Query([], $post, ['pagename' => 'bar']);

        $branch = new BranchPage($postTemplates);

        static::assertSame(['page-meh', 'page-bar', 'page-1', 'page'], $branch->leaves($query));
    }

    public function testLeavesPagePagenameTemplateFolder()
    {
        $post = Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = 'foo';
        $post->post_type = 'page';

        $query = new \WP_Query([], $post, ['pagename' => 'bar']);

        $postTemplates = Mockery::mock(PostTemplates::class);
        $postTemplates
            ->shouldReceive('findFor')
            ->once()
            ->with($post)
            ->andReturn('page-templates/page-meh');

        $branch = new BranchPage($postTemplates);

        $expected = ['page-templates/page-meh', 'page-bar', 'page-1', 'page'];

        static::assertSame($expected, $branch->leaves($query));
    }

    public function testPageNameReturnTemplateIfNoPagename()
    {
        Functions\when('sanitize_title')->alias('strtolower');

        $post = Mockery::mock('\WP_Post');
        $post->ID = 1;
        $post->post_name = '';
        $post->post_title = 'foo';
        $post->post_type = 'page';

        $query = new \WP_Query(['is_preview' => true], $post, ['page_id' => 1, 'pagename' => '']);

        $postTemplates = Mockery::mock(PostTemplates::class);
        $postTemplates
            ->shouldReceive('findFor')
            ->once()
            ->with($post)
            ->andReturn('page-foo');

        $branch = new BranchPage($postTemplates);

        $expected = ['page-foo', 'page-1', 'page'];

        static::assertSame($expected, $branch->leaves($query));
    }
}
