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

use Brain\Hierarchy\Branch\BranchCategory;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class BranchCategoryTest extends TestCase
{
    /**
     * @test
     */
    public function testLeavesNoCategory(): void
    {
        $query = new \WP_Query([], null);
        $branch = new BranchCategory();

        static::assertSame(['category'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeaves(): void
    {
        $category = (object)['slug' => 'foo', 'term_id' => 123];
        $query = new \WP_Query([], $category);

        $branch = new BranchCategory();

        static::assertSame(['category-foo', 'category-123', 'category'], $branch->leaves($query));
    }
}
