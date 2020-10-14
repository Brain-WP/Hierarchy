<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Unit\Finder;

use Brain\Hierarchy\Finder\FoldersTemplateFinder;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class FoldersTemplateFinderTest extends TestCase
{
    public function testFindNothing()
    {
        $folders = [getenv('HIERARCHY_TESTS_BASEPATH').'/files'];
        $finder = new FoldersTemplateFinder($folders);

        static::assertSame('', $finder->find('foo', 'foo'));
    }

    public function testFind()
    {
        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/index.php';

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH').'/files'];
        $finder = new FoldersTemplateFinder($folders);

        static::assertSame($template, $finder->find('index', 'index'));
    }

    public function testFindFirst()
    {
        $folders = [getenv('HIERARCHY_TESTS_BASEPATH').'/files'];
        $finder = new FoldersTemplateFinder($folders);

        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/another.php';

        static::assertSame($template, $finder->findFirst(['page-foo', 'another', 'index'], 'page'));
    }

    public function testFindSeveralExtensions()
    {
        $twigTemplate = getenv('HIERARCHY_TESTS_BASEPATH').'/files/singular.twig';
        $phpTemplate = getenv('HIERARCHY_TESTS_BASEPATH').'/files/singular.php';
        $fallbackTemplate = getenv('HIERARCHY_TESTS_BASEPATH').'/files/single.php';

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH').'/files'];
        $twigFinder = new FoldersTemplateFinder($folders, [' TWIG ', 'php']);
        $phpFinder = new FoldersTemplateFinder($folders, ['.php', 'twig']);

        static::assertSame($twigTemplate, $twigFinder->find('singular', 'singular'));
        static::assertSame($phpTemplate, $phpFinder->find('singular', 'singular'));
        static::assertSame($fallbackTemplate, $twigFinder->find('single', 'single'));
    }

    public function testFindExtensionless()
    {
        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/archive';

        $folders = [getenv('HIERARCHY_TESTS_BASEPATH').'/files'];
        $finder = new FoldersTemplateFinder($folders, '');

        static::assertSame($template, $finder->find('archive', 'archive'));
    }
}
