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

namespace Brain\Hierarchy\Finder;

use Symfony\Component\Finder\Finder;

/**
 * A Symfony Finder adapter. Hierarchy does not ship with Symfony Finder (only on development)
 * so it has to be installed separately.
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class SymfonyFinderAdapter implements TemplateFinder
{
    use FindFirstTrait;

    /**
     * @var Finder
     */
    private $finder;

    /**
     * @param Finder $finder
     */
    public function __construct(Finder $finder)
    {
        $this->finder = $finder;
    }

    /**
     * @param string $template
     * @param string $type
     * @return string
     */
    public function find(string $template, string $type): string
    {
        $name = untrailingslashit(wp_normalize_path($template));
        $finder = clone $this->finder;

        if (substr_count($name, '/')) {
            $finder = $finder->path(dirname($name));
            $name = basename($name);
        }

        /** @var \SplFileInfo $item */
        foreach ($finder->files()->name("{$name}*") as $item) {
            return (string)$item->getRealPath();
        }

        return '';
    }
}
