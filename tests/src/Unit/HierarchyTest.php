<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Unit;

use Brain\Hierarchy\Tests\TestCase;
use Brain\Hierarchy\Tests\Stubs;
use Brain\Hierarchy\Branch\BranchInterface;
use Brain\Hierarchy\Hierarchy;
use Brain\Monkey\Filters;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class HierarchyTest extends TestCase
{

    public function testParse()
    {

        $hierarchy = new Hierarchy();

        $branches = [
            Stubs\BranchStubFoo::class,  // leaves: ['foo', 'bar']
            Stubs\BranchStubBar::class,  // leaves: ['baz', 'bar']
            Stubs\BranchStubBar2::class, // should be skipped because has same name of previous
            Stubs\BranchStubBaz::class,  // should be skipped because its is() always returns false
        ];

        Filters\expectApplied('brain.hierarchy.branches')->once()->andReturn($branches);

        $query = new \WP_Query();

        /** @var \stdClass $data */
        $data = $this->callPrivateFunc('parse', $hierarchy, [$query]);

        $expected = [
            'foo' => (new Stubs\BranchStubFoo())->leaves($query),
            'bar' => (new Stubs\BranchStubBar())->leaves($query),
            'index' => ['index'],
        ];

        $expectedFlat = [
            'foo',
            'bar',
            'baz',
            'index',
        ];

        static::assertInstanceOf(\stdClass::class, $data);
        static::assertSame($expected, $data->hierarchy);
        static::assertSame($expectedFlat, $data->templates);
    }

    public function testBranches()
    {
        $hierarchy = new Hierarchy();
        $classes = $this->getPrivateStaticVar('branches', $hierarchy);

        foreach ($classes as $class) {
            static::assertInstanceOf(BranchInterface::class, new $class());
        }
    }

    public function testGetHierarchy()
    {
        static::assertSame(['index' => ['index']], (new Hierarchy())->getHierarchy(new \WP_Query()));
    }

    public function testGetTemplates()
    {
        static::assertSame(['index'], (new Hierarchy())->getTemplates(new \WP_Query()));
    }
}
