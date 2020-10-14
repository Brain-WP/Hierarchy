<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Functional\Finder;

use Brain\Monkey\Functions;
use Brain\Hierarchy\Finder\SymfonyFinderAdapter;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class SymfonyFinderAdapterTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        Functions\when('trailingslashit')->alias(function ($str) {
            return rtrim($str, '\\/').'/';
        });
        Functions\when('get_stylesheet_directory')->alias(function () {
            return getenv('HIERARCHY_TESTS_BASEPATH').'/files';
        });
        Functions\when('get_template_directory')->alias(function () {
            return getenv('HIERARCHY_TESTS_BASEPATH').'/files';
        });
    }

    public function testFind()
    {
        $template = realpath(getenv('HIERARCHY_TESTS_BASEPATH').'/files/index.php');

        $finder = new SymfonyFinderAdapter();

        static::assertSame($template, $finder->find('index', 'index'));
    }

    public function testFindFirst()
    {
        $template = realpath(getenv('HIERARCHY_TESTS_BASEPATH').'/files/another.php');

        $finder = new SymfonyFinderAdapter();

        static::assertSame($template, $finder->findFirst(['foo', 'another', 'index'], 'index'));
    }

    public function testFindFirstFolders()
    {
        $template = realpath(getenv('HIERARCHY_TESTS_BASEPATH').'/files/it/page.php');

        $finder = new SymfonyFinderAdapter();

        static::assertSame($template, $finder->findFirst(['foo', 'it/page', 'index'], 'page'));
    }
}
