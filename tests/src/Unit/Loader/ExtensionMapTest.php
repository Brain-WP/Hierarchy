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

namespace Brain\Hierarchy\Tests\Unit\Loader;

use Brain\Hierarchy\Loader\ExtensionMap;
use Brain\Hierarchy\Loader\Loader;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class ExtensionMapTest extends TestCase
{
    /**
     * @test
     */
    public function testLoadWithInstances(): void
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/';

        $aLoader = \Mockery::mock(Loader::class);
        $aLoader->expects('load')->with(\Mockery::type('string'))->andReturn('php!');

        $bLoader = \Mockery::mock(Loader::class);
        $bLoader->expects('load')->with(\Mockery::type('string'))->andReturn('twig!');

        $loader = new ExtensionMap([
            'php' => $aLoader,
            'twig' => $bLoader,
        ]);

        static::assertSame('php!', $loader->load($path . 'singular.php'));
        static::assertSame('twig!', $loader->load($path . 'singular.twig'));
    }

    /**
     * @test
     */
    public function testLoadWithFactories(): void
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/';

        $aLoader = \Mockery::mock(Loader::class);
        $aLoader->expects('load')->with(\Mockery::type('string'))->andReturn('php!');

        $bLoader = \Mockery::mock(Loader::class);
        $bLoader->expects('load')->with(\Mockery::type('string'))->andReturn('twig!');

        $loader = new ExtensionMap([
            'php' => static function () use ($aLoader): Loader {
                return $aLoader;
            },
            'twig' => static function () use ($bLoader): Loader {
                return $bLoader;
            },
        ]);

        static::assertSame('php!', $loader->load($path . 'singular.php'));
        static::assertSame('twig!', $loader->load($path . 'singular.twig'));
    }
}
