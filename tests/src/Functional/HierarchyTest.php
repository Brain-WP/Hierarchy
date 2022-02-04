<?php

/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

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
    /**
     * @test
     */
    public function testGetHierarchy(): void
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
            'single' => [
                'single-book-ひら',
                'single-book-%E3%81%B2%E3%82%89',
                'single-book',
                'single',
            ],
            'singular' => [
                'singular',
            ],
            'index' => [
                'index',
            ],
        ];

        $actual = $hierarchy->hierarchy($query);

        static::assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testGetHierarchyFiltered(): void
    {
        Filters\expectApplied('brain.hierarchy.branches')
            ->once()
            ->andReturnUsing(static function (array $branches): array {
                unset($branches['singular']);

                return $branches;
            });

        Filters\expectApplied('index_template_hierarchy')
            ->once()
            ->andReturnUsing(static function (array $leaves): array {
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
            'single' => [
                'single-book-ひら',
                'single-book-%E3%81%B2%E3%82%89',
                'single-book',
                'single',
            ],
            'singular' => [
                'singular',
            ],
            'index' => [
                'index',
                'jolly',
            ],
        ];

        $actual = $hierarchy->hierarchy($query);

        static::assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function testGetHierarchyNotAppliesFiltersIfNotFiltered(): void
    {
        Filters\expectApplied('index_template_hierarchy')
            ->zeroOrMoreTimes()
            ->andReturnUsing(static function (array $leaves): array {
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
            'single' => [
                'single-book-ひら',
                'single-book-%E3%81%B2%E3%82%89',
                'single-book',
                'single',
            ],
            'singular' => [
                'singular',
            ],
            'index' => [
                'index',
            ],
        ];

        $actual = $hierarchy->hierarchy($query);

        static::assertSame($expected, $actual);
    }
}
