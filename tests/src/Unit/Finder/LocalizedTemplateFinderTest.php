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

use Brain\Monkey\Functions;
use Brain\Hierarchy\Finder\CallbackTemplateFinder;
use Brain\Hierarchy\Finder\LocalizedTemplateFinder;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class LocalizedTemplateFinderTest extends TestCase
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
        Functions\when('get_locale')->alias(function () {
            return 'it_IT';
        });
    }

    public function testFindNothing()
    {
        $callbackFinder = new CallbackTemplateFinder(function () {
            return '';
        });
        $finder = new LocalizedTemplateFinder($callbackFinder);

        static::assertSame('', $finder->find('foo', 'foo'));
    }

    public function testFind()
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH').'/files';
        $callbackFinder = new CallbackTemplateFinder(function ($name) use ($path) {
            return file_exists("{$path}/{$name}.php") ? "{$path}/{$name}.php" : '';
        });

        $finder = new LocalizedTemplateFinder($callbackFinder);

        static::assertSame("{$path}/it/page.php", $finder->find('page', 'page'));
        static::assertSame("{$path}/it_IT/single.php", $finder->find('single', 'single'));
    }

    public function testFindFirst()
    {
        $path = getenv('HIERARCHY_TESTS_BASEPATH').'/files';
        $callbackFinder = new CallbackTemplateFinder(function ($name) use ($path) {
            return file_exists("{$path}/{$name}.php") ? "{$path}/{$name}.php" : '';
        });

        $finder = new LocalizedTemplateFinder($callbackFinder);

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
