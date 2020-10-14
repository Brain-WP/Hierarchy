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

use Brain\Hierarchy\Branch\BranchTag;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class BranchTagTest extends TestCase
{
    public function testLeavesNoTag()
    {
        $query = new \WP_Query();
        $branch = new BranchTag();

        static::assertSame(['tag'], $branch->leaves($query));
    }

    public function testLeaves()
    {
        $tag = (object) ['slug' => 'foo', 'term_id' => 123];
        $query = new \WP_Query([], $tag);

        $branch = new BranchTag();
        static::assertSame(['tag-foo', 'tag-123', 'tag'], $branch->leaves($query));
    }
}
