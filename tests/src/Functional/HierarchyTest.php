<?php # -*- coding: utf-8 -*-
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Functional;

use Brain\Hierarchy\Hierarchy;
use Brain\Hierarchy\Tests\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @package Hierarchy
 * @license http://opensource.org/licenses/MIT MIT
 */
class HierarchyTest extends TestCase
{

    public function testGetHierarchy()
    {
        $post = \Mockery::mock('WP_Post');
        $post->ID = 1;
        $post->post_name = '%E3%81%B2%E3%82%89';
        $post->post_type = 'book';

        Functions\when('get_page_template_slug')->justReturn(false);

        $query = new \WP_Query(
            ['is_single' => true, 'is_singular' => true],
            $post,
            ['p' => 1]
        );

        $hierarchy = new Hierarchy();

        $expected = [
            'single'   => [
                'single-book-ひら',
                'single-book-%E3%81%B2%E3%82%89',
                'single-book',
                'single',
            ],
            'singular' => [
                'singular'
            ],
            'index'    => [
                'index'
            ],
        ];

        $actual = $hierarchy->getHierarchy($query);

        static::assertSame($expected, $actual);
    }

    public function testGetHierarchyFiltered()
    {
        Filters\expectApplied('brain.hierarchy.branches')
               ->once()
               ->andReturnUsing(function (array $branches) {
                   unset($branches['singular']);

                   return $branches;
               });

        Filters\expectApplied('index_template_hierarchy')
               ->once()
               ->andReturnUsing(function (array $leaves) {
                   $leaves[] = 'jolly';

                   return $leaves;
               });

        Functions\when('get_page_template_slug')->justReturn(false);

        $post = \Mockery::mock('WP_Post');
        $post->ID = 1;
        $post->post_name = '%E3%81%B2%E3%82%89';
        $post->post_type = 'book';

        $query = new \WP_Query(
            ['is_single' => true, 'is_singular' => true],
            $post,
            ['p' => 1]
        );

        $hierarchy = new Hierarchy();

        $expected = [
            'single'   => [
                'single-book-ひら',
                'single-book-%E3%81%B2%E3%82%89',
                'single-book',
                'single',
            ],
            'index'    => [
                'index',
                'jolly'
            ],
        ];

        $actual = $hierarchy->getHierarchy($query);

        static::assertSame($expected, $actual);
    }

    public function testGetHierarchyNotAppliesFiltersIfNotFiltered()
    {
        Filters\expectApplied('index_template_hierarchy')
               ->zeroOrMoreTimes()
               ->andReturnUsing(function (array $leaves) {
                   $leaves[] = 'jolly';

                   return $leaves;
               });

        Functions\when('get_page_template_slug')->justReturn(false);

        $post = \Mockery::mock('WP_Post');
        $post->ID = 1;
        $post->post_name = '%E3%81%B2%E3%82%89';
        $post->post_type = 'book';

        $query = new \WP_Query(
            ['is_single' => true, 'is_singular' => true],
            $post,
            ['p' => 1]
        );

        $hierarchy = new Hierarchy(Hierarchy::NOT_FILTERABLE);

        $expected = [
            'single'   => [
                'single-book-ひら',
                'single-book-%E3%81%B2%E3%82%89',
                'single-book',
                'single',
            ],
            'singular' => [
                'singular'
            ],
            'index'    => [
                'index'
            ],
        ];

        $actual = $hierarchy->getHierarchy($query);

        static::assertSame($expected, $actual);
    }
}
