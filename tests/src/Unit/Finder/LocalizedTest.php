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

use Brain\Monkey\Functions;
use Brain\Hierarchy\Finder\ByCallback;
use Brain\Hierarchy\Finder\Localized;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class LocalizedTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('get_stylesheet_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH');
        });
        Functions\when('get_template_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH');
        });
        Functions\when('get_locale')->alias(static function (): string {
            return 'it_IT';
        });
    }

    /**
     * @test
     */
    public function testFindNothing(): void
    {
        $callbackFinder = new ByCallback(static function (): string {
            return '';
        });
        $finder = new Localized($callbackFinder);

        static::assertSame('', $finder->find('foo', 'foo'));
    }

    /**
     * @test
     */
    public function testFind(): void
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH') . '/files';
        $callbackFinder = new ByCallback(static function (string $name) use ($path): string {
            return file_exists("{$path}/{$name}.php") ? "{$path}/{$name}.php" : '';
        });

        $finder = new Localized($callbackFinder);

        static::assertSame("{$path}/it/page.php", $finder->find('page', 'page'));
        static::assertSame("{$path}/it_IT/single.php", $finder->find('single', 'single'));
    }

    /**
     * @test
     */
    public function testFindFirst(): void
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH') . '/files';
        $callbackFinder = new ByCallback(static function (string $name) use ($path): string {
            return file_exists("{$path}/{$name}.php") ? "{$path}/{$name}.php" : '';
        });

        $finder = new Localized($callbackFinder);

        static::assertSame(
            "{$path}/it/page.php",
            $finder->findFirst(['foo', 'page', 'bar'], 'page')
        );
        static::assertSame(
            "{$path}/it_IT/single.php",
            $finder->findFirst(['foo', 'single', 'bar'], 'single')
        );
        static::assertSame(
            "{$path}/another.php",
            $finder->findFirst(['foo', 'meh', 'another'], 'foo')
        );
    }
}
