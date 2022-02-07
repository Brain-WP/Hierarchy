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

use Brain\Hierarchy\Finder\ByFolders;
use Brain\Hierarchy\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class ByFoldersTest extends TestCase
{
    /**
     * @test
     */
    public function testFindNothing(): void
    {
        $folders = [getenv('HIERARCHY_TESTS_BASEPATH') . '/files'];
        $finder = new ByFolders($folders);

        static::assertSame('', $finder->find('foo', 'foo'));
    }

    /**
     * @test
     */
    public function testFind(): void
    {
        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/index.php';

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH') . '/files'];
        $finder = new ByFolders($folders);

        static::assertSame($template, $finder->find('index', 'index'));
    }

    /**
     * @test
     */
    public function testFindComposedExtension(): void
    {
        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/composed.html.php';

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH') . '/files'];
        $finder = new ByFolders($folders, 'html.php');

        static::assertSame($template, $finder->find('composed', 'index'));
    }

    /**
     * @test
     */
    public function testFindFirst(): void
    {
        $folders = [getenv('HIERARCHY_TESTS_BASEPATH') . '/files'];
        $finder = new ByFolders($folders);

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

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH') . '/files'];
        $twigFinder = new ByFolders($folders, ' TWIG ', 'php');
        $phpFinder = new ByFolders($folders, '.php', 'twig');

        static::assertSame($twigTemplate, $twigFinder->find('singular', 'singular'));
        static::assertSame($phpTemplate, $phpFinder->find('singular', 'singular'));
        static::assertSame($fallbackTemplate, $twigFinder->find('single', 'single'));
    }

    /**
     * @test
     */
    public function testFindExtensionless(): void
    {
        $template = getenv('HIERARCHY_TESTS_BASEPATH') . '/files/archive';

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH') . '/files'];
        $finder = new ByFolders($folders, '');

        static::assertSame($template, $finder->find('archive', 'archive'));
    }
}
