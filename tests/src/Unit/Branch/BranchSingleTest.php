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

use Brain\Hierarchy\Branch\BranchSingle;
use Brain\Hierarchy\PostTemplates;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class BranchSingleTest extends TestCase
{
    /**
     * @test
     */
    public function testLeavesNoPost(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 0;
        $post->post_name = '';
        $post->post_type = '';
        $query = new \WP_Query([], $post);

        $branch = new BranchSingle();

        static::assertSame(['single'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeavesNoTemplate(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 123;
        $post->post_name = 'one_two_three';
        $post->post_type = 'my_cpt';
        $query = new \WP_Query([], $post);

        $postTemplates = \Mockery::mock(PostTemplates::class);
        $postTemplates
            ->expects('findFor')
            ->with($post)
            ->andReturn('');

        $branch = new BranchSingle($postTemplates);

        $expected = [
            'single-my_cpt-one_two_three',
            'single-my_cpt',
            'single',
        ];

        static::assertSame($expected, $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeavesPostTemplate(): void
    {
        $post = \Mockery::mock('\WP_Post');
        $post->ID = 123;
        $post->post_name = 'one_two_three';
        $post->post_type = 'my_cpt';
        $query = new \WP_Query([], $post);

        $postTemplates = \Mockery::mock(PostTemplates::class);
        $postTemplates
            ->expects('findFor')
            ->with($post)
            ->andReturn('templates/foo');

        $branch = new BranchSingle($postTemplates);

        $expected = [
            'templates/foo',
            'single-my_cpt-one_two_three',
            'single-my_cpt',
            'single',
        ];

        static::assertSame($expected, $branch->leaves($query));
    }
}
