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

namespace Brain\Hierarchy\Tests\Functional\Finder;

use Brain\Monkey\Functions;
use Brain\Hierarchy\Finder\SymfonyFinderAdapter;
use Brain\Hierarchy\Tests\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class SymfonyFinderAdapterTest extends TestCase
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        Functions\when('get_stylesheet_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH') . '/files';
        });
        Functions\when('get_template_directory')->alias(static function (): string {
            return getenv('HIERARCHY_TESTS_BASEPATH') . '/files';
        });
    }

    /**
     * @test
     */
    public function testFind(): void
    {
        $finder = new SymfonyFinderAdapter($this->factoryFinder());

        $template = realpath(getenv('HIERARCHY_TESTS_BASEPATH') . '/files/index.php');

        static::assertSame($template, $finder->find('index', 'index'));
    }

    /**
     * @test
     */
    public function testFindFirst(): void
    {
        $finder = new SymfonyFinderAdapter($this->factoryFinder());

        $template = realpath(getenv('HIERARCHY_TESTS_BASEPATH') . '/files/singular.php');

        static::assertSame($template, $finder->findFirst(['foo', 'singular', 'index'], 'index'));
    }

    /**
     * @test
     */
    public function testFindFirstFolders(): void
    {
        $finder = new SymfonyFinderAdapter($this->factoryFinder());

        $template = realpath(getenv('HIERARCHY_TESTS_BASEPATH') . '/files/it_IT/single.php');

        static::assertSame($template, $finder->findFirst(['foo', 'it_IT/single', 'index'], 'page'));
    }

    /**
     * @return Finder
     */
    private function factoryFinder(): Finder
    {
        return Finder::create()
            ->in([get_stylesheet_directory(), get_template_directory()])
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs(true);
    }
}
