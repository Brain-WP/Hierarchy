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

use Brain\Hierarchy\Finder\SubfolderTemplateFinder;
use Brain\Hierarchy\Tests\TestCase;
use Brain\Monkey\Functions;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class SubfolderTemplateFinderTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Functions\when('get_stylesheet_directory')->alias(function () {
            return getenv('HIERARCHY_TESTS_BASEPATH');
        });
        Functions\when('get_template_directory')->alias(function () {
            return getenv('HIERARCHY_TESTS_BASEPATH');
        });
    }

    public function testFindNothing()
    {
        $finder = new SubfolderTemplateFinder('files');

        static::assertSame('', $finder->find('foo', 'foo'));
    }

    public function testFind()
    {
        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/index.php';
        $finder = new SubfolderTemplateFinder('files');

        static::assertSame($template, $finder->find('index', 'index'));
    }

    public function testFindFirst()
    {
        $finder = new SubfolderTemplateFinder('files');
        $template = getenv('HIERARCHY_TESTS_BASEPATH').'/files/another.php';

        static::assertSame($template, $finder->findFirst(['page-foo', 'another', 'index'], 'page'));
    }

    public function testFindSeveralExtensions()
    {
        $twigTemplate = getenv('HIERARCHY_TESTS_BASEPATH').'/files/singular.twig';
        $phpTemplate = getenv('HIERARCHY_TESTS_BASEPATH').'/files/singular.php';
        $fallbackTemplate = getenv('HIERARCHY_TESTS_BASEPATH').'/files/single.php';
        $twigFinder = new SubfolderTemplateFinder('files', ['twig', 'php']);
        $phpFinder = new SubfolderTemplateFinder('files', ['php', 'twig']);
        static::assertSame($twigTemplate, $twigFinder->find('singular', 'singular'));
        static::assertSame($phpTemplate, $phpFinder->find('singular', 'singular'));
        static::assertSame($fallbackTemplate, $twigFinder->find('single', 'single'));
    }
}
