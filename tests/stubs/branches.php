<?php
/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Tests\Stubs;

use Brain\Hierarchy\Branch\Branch;

class BranchStubFoo implements Branch
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'foo';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return true;
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        return ['foo', 'bar'];
    }
}

class BranchStubBar implements Branch
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'bar';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return true;
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        return ['baz', 'bar'];
    }
}

class BranchStubBar2 implements Branch
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'bar';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return true;
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        return ['a', 'b', 'c'];
    }
}

class BranchStubBaz implements Branch
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'baz';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return false;
    }

    /**
     * @param \WP_Query $query
     * @return array
     */
    public function leaves(\WP_Query $query): array
    {
        return ['1', '2', 3];
    }
}
