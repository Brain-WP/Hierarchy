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

namespace Brain\Hierarchy\Loader;

/**
 * An aggregate loader with loading predicates priority based on order of addition (FIFO).
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class Cascade implements Aggregate
{
    /**
     * @var list<array{Loader|callable, callable}>
     */
    private $loaders = [];

    /**
     * @param Loader $loader
     * @param callable $predicate
     * @return Aggregate
     */
    public function addLoader(Loader $loader, callable $predicate): Aggregate
    {
        $this->loaders[] = [$loader, $predicate];

        return $this;
    }

    /**
     * @param callable $loaderFactory
     * @param callable $predicate
     * @return Aggregate
     */
    public function addLoaderFactory(callable $loaderFactory, callable $predicate): Aggregate
    {
        $this->loaders[] = [$loaderFactory, $predicate];

        return $this;
    }

    /**
     * @param string $templatePath
     * @return string
     */
    public function load(string $templatePath): string
    {
        $loaders = $this->loaders;

        foreach ($loaders as [$loader, $predicate]) {
            if (!$predicate($templatePath)) {
                continue;
            }

            is_callable($loader) and $loader = $loader();
            if ($loader instanceof Loader) {
                return $loader->load($templatePath);
            }
        }

        return '';
    }
}
