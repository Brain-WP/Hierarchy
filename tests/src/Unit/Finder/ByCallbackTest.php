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

namespace Brain\Hierarchy\Tests\Unit\Finder;

use Brain\Hierarchy\Finder\ByCallback;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class ByCallbackTest extends TestCase
{
    /**
     * @test
     */
    public function testFindNothing(): void
    {
        $finder = new ByCallback(static function (): string {
            return '';
        });
        static::assertSame('', $finder->find('index', 'index'));
    }

    /**
     * @test
     */
    public function testFind(): void
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/';
        $finder = new ByCallback(static function (string $name, string $type) use ($path): string {
            static::assertSame('index', $type);

            return "{$path}{$name}.php";
        });

        static::assertSame("{$path}index.php", $finder->find('index', 'index'));
    }

    /**
     * @test
     */
    public function testFindFirst(): void
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/';
        $finder = new ByCallback(static function (string $name, string $type) use ($path): string {
            static::assertSame('page', $type);
            if (file_exists("{$path}{$name}.php")) {
                return "{$path}{$name}.php";
            }

            return '';
        });

        static::assertSame("{$path}another.php", $finder->findFirst(['meh', 'another'], 'page'));
    }
}
