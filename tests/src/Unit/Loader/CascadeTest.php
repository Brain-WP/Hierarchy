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

use Brain\Hierarchy\Loader\Cascade;
use Brain\Hierarchy\Loader\Loader;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class CascadeTest extends TestCase
{
    /**
     * @test
     */
    public function testLoadReturnEmptyIfNoLoaders(): void
    {
        $loader = new Cascade();
        static::assertSame('', $loader->load('foo'));
    }

    /**
     * @test
     */
    public function testLoadWithAddedLoader(): void
    {
        $innerLoader = \Mockery::mock(Loader::class);
        $innerLoader->expects('load')->with('/a/path')->andReturn('Loaded!');

        $predicate = static function (string $path): bool {
            return $path === '/a/path';
        };

        $loader = new Cascade();
        $loader->addLoader($innerLoader, $predicate);

        static::assertSame('Loaded!', $loader->load('/a/path'));
        static::assertSame('', $loader->load('/another/path'));
    }

    /**
     * @test
     */
    public function testLoadWithAddedLoaderFactory(): void
    {
        $factory = static function (): Loader {
            static $counter = 0;
            ++$counter;
            if ($counter > 1) {
                throw new \Exception('Loader factory should be called once.');
            }
            $loader = \Mockery::mock(Loader::class);
            $loader->expects('load')->with('/a/path')->andReturn('Loaded!');

            return $loader;
        };

        $predicate = static function (string $path): bool {
            return $path === '/a/path';
        };

        $loader = new Cascade();
        $loader->addLoaderFactory($factory, $predicate);

        static::assertSame('Loaded!', $loader->load('/a/path'));
        static::assertSame('', $loader->load('/another/path'));
    }

    /**
     * @test
     */
    public function testLoadPriority(): void
    {
        $factory = static function (): Loader {
            static $counter = 0;
            ++$counter;
            if ($counter > 1) {
                throw new \Exception('Loader factory should be called once.');
            }
            $loader = \Mockery::mock(Loader::class);
            $loader->expects('load')->with('/a/path')->andReturn('A!');

            return $loader;
        };

        $innerLoader = \Mockery::mock(Loader::class);
        $innerLoader->expects('load')
            ->with(\Mockery::type('string'))
            ->andReturn('B!');

        $aPredicate = static function (string $path): bool {
            return $path === '/a/path';
        };

        $bPredicate = static function (): bool {
            return true;
        };

        $loader = new Cascade();
        $loader
            ->addLoaderFactory($factory, $aPredicate)
            ->addLoader($innerLoader, $bPredicate);

        static::assertSame('A!', $loader->load('/a/path'));
        static::assertSame('B!', $loader->load('/another/path'));
    }
}
