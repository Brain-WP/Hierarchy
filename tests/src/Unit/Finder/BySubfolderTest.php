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

use Brain\Hierarchy\Finder\BySubfolder;
use Brain\Hierarchy\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class BySubfolderTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Functions\when('get_stylesheet_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH');
        });
        Functions\when('get_template_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH');
        });
    }

    /**
     * @test
     */
    public function testFindNothing(): void
    {
        $finder = new BySubfolder('files');

        static::assertSame('', $finder->find('foo', 'foo'));
    }

    /**
     * @test
     */
    public function testFind(): void
    {
        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/index.php';
        $finder = new BySubfolder('files');

        static::assertSame($template, $finder->find('index', 'index'));
    }

    /**
     * @test
     */
    public function testFindFirst(): void
    {
        $finder = new BySubfolder('files');
        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/another.php';

        static::assertSame($template, $finder->findFirst(['page-foo', 'another', 'index'], 'page'));
    }

    /**
     * @test
     */
    public function testFindSeveralExtensions(): void
    {
        $twigTemplate = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/singular.twig';
        $phpTemplate = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/singular.php';
        $fallbackTemplate = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/single.php';
        $twigFinder = new BySubfolder('files', 'twig', 'php');
        $phpFinder = new BySubfolder('files', 'php', 'twig');
        static::assertSame($twigTemplate, $twigFinder->find('singular', 'singular'));
        static::assertSame($phpTemplate, $phpFinder->find('singular', 'singular'));
        static::assertSame($fallbackTemplate, $twigFinder->find('single', 'single'));
    }
}
