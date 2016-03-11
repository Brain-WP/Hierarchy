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

use Brain\Monkey\Functions;
use Brain\Hierarchy\Branch\BranchPostTypeArchive;
use Brain\Hierarchy\Tests\TestCase;
use Mockery;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package Hierarchy
 */
final class BranchPostTypeArchiveTest extends TestCase
{
    public function testLeavesWithArchiveCpt()
    {
        $query = Mockery::mock('\WP_Query');
        $query->shouldReceive('is_post_type_archive')->andReturn(true);
        $query->shouldReceive('get')->with('post_type')->andReturn('my_cpt');
        Functions::expect('get_post_type_object')->with('my_cpt')->andReturn((object) ['has_archive' => true]);

        $branch = new BranchPostTypeArchive();

        assertTrue($branch->is($query));
        assertSame(['archive-my_cpt', 'archive'], $branch->leaves($query));
    }

    public function testLeavesWithNoArchiveCpt()
    {
        $query = Mockery::mock('\WP_Query');
        $query->shouldReceive('is_post_type_archive')->andReturn(true);
        $query->shouldReceive('get')->with('post_type')->andReturn('my_cpt');
        Functions::expect('get_post_type_object')->with('my_cpt')->andReturn((object) ['has_archive' => false]);

        $branch = new BranchPostTypeArchive();

        assertFalse($branch->is($query));
        assertSame(['archive'], $branch->leaves($query));
    }
}
