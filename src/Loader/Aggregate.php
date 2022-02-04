<?php

/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Hierarchy\Loader;

/**
 * An "aggregate" loader that allows a different loader based on "predicates".
 *
 * Predicates are callbacks that receive template path and return a boolean.
 * When a predicate returns true, the related loader is used to load the template.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
interface Aggregate extends Loader
{
    /**
     * Append a loader object to be used to load templates when given predicate
     * returns true when receiving the template path.
     *
     * @param Loader $loader
     * @param callable $predicate
     * @return Aggregate
     */
    public function addLoader(Loader $loader, callable $predicate): Aggregate;

    /**
     * Append a loader factory to be used to instantiate a loader that is used to load templates
     * when given predicate returns true when receiving the template path.
     *
     * @param callable $loaderFactory
     * @param callable $predicate
     * @return Aggregate
     */
    public function addLoaderFactory(callable $loaderFactory, callable $predicate): Aggregate;
}
