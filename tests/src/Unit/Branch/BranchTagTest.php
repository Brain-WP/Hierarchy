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

use Brain\Hierarchy\Branch\BranchTag;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class BranchTagTest extends TestCase
{
    /**
     * @test
     */
    public function testLeavesNoTag(): void
    {
        $query = new \WP_Query();
        $branch = new BranchTag();

        static::assertSame(['tag'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeaves(): void
    {
        $tag = (object)['slug' => 'foo', 'term_id' => 123];
        $query = new \WP_Query([], $tag);

        $branch = new BranchTag();
        static::assertSame(['tag-foo', 'tag-123', 'tag'], $branch->leaves($query));
    }
}
