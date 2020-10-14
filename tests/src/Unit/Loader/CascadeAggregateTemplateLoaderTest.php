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

use Brain\Hierarchy\Loader\CascadeAggregateTemplateLoader;
use Brain\Hierarchy\Loader\TemplateLoaderInterface;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class CascadeAggregateTemplateLoaderTest extends TestCase
{
    public function testLoadReturnEmptyIfNoLoaders()
    {
        $loader = new CascadeAggregateTemplateLoader();
        static::assertSame('', $loader->load('foo'));
    }

    public function testLoadWithAddedLoader()
    {
        $innerLoader = \Mockery::mock(TemplateLoaderInterface::class);
        $innerLoader->shouldReceive('load')->once()->with('/a/path')->andReturn('Loaded!');

        $predicate = function ($path) {
            return $path === '/a/path';
        };

        $loader = new CascadeAggregateTemplateLoader();
        $loader->addLoader($innerLoader, $predicate);

        static::assertSame('Loaded!', $loader->load('/a/path'));
        static::assertSame('', $loader->load('/another/path'));
    }

    public function testLoadWithAddedLoaderFactory()
    {
        $factory = function () {
            static $n = 0;
            ++$n;
            if ($n > 1) {
                throw new \PHPUnit_Framework_Exception('Loader factory should be called once.');
            }
            $loader = \Mockery::mock(TemplateLoaderInterface::class);
            $loader->shouldReceive('load')->once()->with('/a/path')->andReturn('Loaded!');

            return $loader;
        };

        $predicate = function ($path) {
            return $path === '/a/path';
        };

        $loader = new CascadeAggregateTemplateLoader();
        $loader->addLoaderFactory($factory, $predicate);

        static::assertSame('Loaded!', $loader->load('/a/path'));
        static::assertSame('', $loader->load('/another/path'));
    }

    public function testLoadPriority()
    {
        $factory = function () {
            static $n = 0;
            ++$n;
            if ($n > 1) {
                throw new \PHPUnit_Framework_Exception('Loader factory should be called once.');
            }
            $loader = \Mockery::mock(TemplateLoaderInterface::class);
            $loader->shouldReceive('load')->once()->with('/a/path')->andReturn('A!');

            return $loader;
        };

        $innerLoader = \Mockery::mock(TemplateLoaderInterface::class);
        $innerLoader->shouldReceive('load')->once()->with(\Mockery::type('string'))->andReturn('B!');

        $aPredicate = function ($path) {
            return $path === '/a/path';
        };

        $bPredicate = function () {
            return true;
        };

        $loader = new CascadeAggregateTemplateLoader();
        $loader
            ->addLoaderFactory($factory, $aPredicate)
            ->addLoader($innerLoader, $bPredicate);

        static::assertSame('A!', $loader->load('/a/path'));
        static::assertSame('B!', $loader->load('/another/path'));
    }
}
