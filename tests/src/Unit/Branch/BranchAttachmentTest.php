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

use Brain\Hierarchy\Branch\BranchAttachment;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class BranchAttachmentTest extends TestCase
{
    /**
     * @test
     */
    public function testLeavesNoPost(): void
    {
        $branch = new BranchAttachment();
        $post = \Mockery::mock('\WP_Post');
        $post->post_mime_type = '';
        $wpQuery = new \WP_Query(['is_attachment'], $post);

        static::assertSame(['attachment'], $branch->leaves($wpQuery));
    }

    /**
     * @test
     */
    public function testLeaves(): void
    {
        $branch = new BranchAttachment();
        $post = \Mockery::mock('\WP_Post');
        $post->post_mime_type = 'image/jpeg';
        $wpQuery = new \WP_Query(['is_attachment'], $post);
        $wpQuery->post = $post;

        static::assertSame(
            ['image', 'jpeg', 'image_jpeg', 'attachment'],
            $branch->leaves($wpQuery)
        );
    }
}
