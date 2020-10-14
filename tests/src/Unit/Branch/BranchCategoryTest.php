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

use Brain\Hierarchy\Branch\BranchCategory;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class BranchCategoryTest extends TestCase
{
    public function testLeavesNoCategory()
    {
        $query = new \WP_Query([], null);
        $branch = new BranchCategory();

        static::assertSame(['category'], $branch->leaves($query));
    }

    public function testLeaves()
    {
        $category = (object) ['slug' => 'foo', 'term_id' => 123];
        $query = new \WP_Query([], $category);

        $branch = new BranchCategory();

        static::assertSame(['category-foo', 'category-123', 'category'], $branch->leaves($query));
    }
}
