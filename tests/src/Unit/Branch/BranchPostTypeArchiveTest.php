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

use Brain\Monkey\Functions;
use Brain\Hierarchy\Branch\BranchPostTypeArchive;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class BranchPostTypeArchiveTest extends TestCase
{
    /**
     * @test
     */
    public function testLeavesWithArchiveCpt(): void
    {
        /** @var \WP_Query|\Mockery\MockInterface $query */
        $query = \Mockery::mock('\WP_Query');
        $query->expects('is_post_type_archive')->andReturn(true);
        $query->shouldReceive('get')->with('post_type')->zeroOrMoreTimes()->andReturn('my_cpt');

        $cpt = \Mockery::mock('\WP_Post_Type');
        $cpt->has_archive = true;
        Functions\expect('get_post_type_object')->with('my_cpt')->andReturn($cpt);

        $branch = new BranchPostTypeArchive();

        static::assertTrue($branch->is($query));
        static::assertSame(['archive-my_cpt', 'archive'], $branch->leaves($query));
    }

    /**
     * @test
     */
    public function testLeavesWithNoArchiveCpt(): void
    {
        /** @var \WP_Query|\Mockery\MockInterface $query */
        $query = \Mockery::mock('\WP_Query');
        $query->expects('is_post_type_archive')->andReturn(true);
        $query->shouldReceive('get')->with('post_type')->zeroOrMoreTimes()->andReturn('my_cpt');
        Functions\expect('get_post_type_object')
            ->with('my_cpt')
            ->andReturn((object)['has_archive' => false]);

        $branch = new BranchPostTypeArchive();

        static::assertFalse($branch->is($query));
        static::assertSame(['archive'], $branch->leaves($query));
    }
}
