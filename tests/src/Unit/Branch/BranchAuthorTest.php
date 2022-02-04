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

use Brain\Hierarchy\Branch\BranchAuthor;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class BranchAuthorTest extends TestCase
{
    /**
     * @test
     */
    public function testLeavesNoUser(): void
    {
        $branch = new BranchAuthor();
        $query = new \WP_Query([], null);

        static::assertSame(['author'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeaves(): void
    {
        $user = \Mockery::mock('\WP_User');
        $user->ID = 12;
        $user->user_nicename = 'john_doe';
        $query = new \WP_Query([], $user);

        $branch = new BranchAuthor();

        static::assertSame(['author-john_doe', 'author-12', 'author'], $branch->leaves($query));
    }
}
