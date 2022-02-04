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

use Brain\Hierarchy\Tests\TestCase;
use Brain\Hierarchy\Tests\Stubs;
use Brain\Hierarchy\Hierarchy;
use Brain\Monkey\Filters;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class HierarchyTest extends TestCase
{
    /**
     * @test
     */
    public function testFilteredBranches(): void
    {
        $branches = [
            Stubs\BranchStubFoo::class,  // leaves: foo, bar
            Stubs\BranchStubBar::class,  // leaves: baz, bar
            Stubs\BranchStubBar2::class, // should be skipped because has the same name as previous
            Stubs\BranchStubBaz::class,  // should be skipped because its is() always returns false
        ];

        Filters\expectApplied('brain.hierarchy.branches')->twice()->andReturn($branches);

        $query = new \WP_Query();
        $hierarchy = new Hierarchy();

        $expectedNested = [
            'foo' => ['foo', 'bar'],
            'bar' => ['baz', 'bar'],
            'index' => ['index'],
        ];

        $expectedFlat = [
            'foo',
            'bar',
            'baz',
            'index',
        ];

        static::assertSame($expectedNested, $hierarchy->hierarchy($query));
        static::assertSame($expectedFlat, $hierarchy->templates($query));
    }

    /**
     * @test
     */
    public function testHierarchy(): void
    {
        static::assertSame(
            ['index' => ['index']],
            (new Hierarchy())->hierarchy(new \WP_Query())
        );
    }

    /**
     * @test
     */
    public function testTemplates(): void
    {
        static::assertSame(['index'], (new Hierarchy())->templates(new \WP_Query()));
    }
}
