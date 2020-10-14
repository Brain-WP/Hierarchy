<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Unit\Loader;

use Brain\Hierarchy\Loader\ExtensionMapTemplateLoader;
use Brain\Hierarchy\Loader\TemplateLoaderInterface;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class ExtensionMapTemplateLoaderTest extends TestCase
{
    public function testLoadWithInstances()
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH').'/files/';

        $aLoader = \Mockery::mock(TemplateLoaderInterface::class);
        $aLoader->shouldReceive('load')->once()->with(\Mockery::type('string'))->andReturn('php!');

        $bLoader = \Mockery::mock(TemplateLoaderInterface::class);
        $bLoader->shouldReceive('load')->once()->with(\Mockery::type('string'))->andReturn('twig!');

        $loader = new ExtensionMapTemplateLoader([
            'php' => $aLoader,
            'twig' => $bLoader,
        ]);

        static::assertSame('php!', $loader->load($path.'singular.php'));
        static::assertSame('twig!', $loader->load($path.'singular.twig'));
    }

    public function testLoadWithFactories()
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH').'/files/';

        $aLoader = \Mockery::mock(TemplateLoaderInterface::class);
        $aLoader->shouldReceive('load')->once()->with(\Mockery::type('string'))->andReturn('php!');

        $bLoader = \Mockery::mock(TemplateLoaderInterface::class);
        $bLoader->shouldReceive('load')->once()->with(\Mockery::type('string'))->andReturn('twig!');

        $loader = new ExtensionMapTemplateLoader([
            'php' => function () use ($aLoader) {
                return $aLoader;
            },
            'twig' => function () use ($bLoader) {
                return $bLoader;
            },
        ]);

        static::assertSame('php!', $loader->load($path.'singular.php'));
        static::assertSame('twig!', $loader->load($path.'singular.twig'));
    }
}
