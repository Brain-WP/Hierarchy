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

use Brain\Hierarchy\FileExtensionPredicate;

/**
 * This is an aggregate loader that allow a different loader based on template file extension.
 * It requires a "map" of extension to loader to be passed to constructor.
 * The map keys are the  template file extensions, the values are the loader to be used.
 * Loader can be passed as:
 * - template loader instances
 * - template loader fully qualified class names
 * - factory callbacks that once called return template loader instances.
 *
 * The same loader can be used for multiple file extension, using as key a string of many extensions
 * separated by a pipe `|`.
 *
 * Example:
 *
 * <code>
 * $loader = new ExtensionMapTemplateLoader([
 *      'php|phtml' => new FileRequireLoader(),
 *      'mustache'  => function() {
 *          return new MyMustacheAdapter(new \Mustache_Engine);
 *       },
 *      'md'        => MyMarkdownRenderer::class
 * ]);
 * </code>
 *
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class ExtensionMap implements Aggregate
{
    /**
     * @var Aggregate
     */
    private $loader;

    /**
     * @param array<string, class-string<Loader>|callable|Loader> $map
     * @param Aggregate|null $loader
     */
    public function __construct(array $map, ?Aggregate $loader = null)
    {
        $this->loader = $loader ?: new Cascade();
        foreach ($map as $extensions => $loader) {
            if ($extensions && is_string($extensions)) {
                $this->maybeExtendLoader($loader, new FileExtensionPredicate($extensions));
            }
        }
    }

    /**
     * @param string $templatePath
     * @return string
     */
    public function load(string $templatePath): string
    {
        return $this->loader->load($templatePath);
    }

    /**
     * @param Loader $loader
     * @param callable $predicate
     * @return Aggregate
     */
    public function addLoader(
        Loader $loader,
        callable $predicate
    ): Aggregate {

        return $this->loader->addLoader($loader, $predicate);
    }

    /**
     * @param callable $loaderFactory
     * @param callable $predicate
     * @return Aggregate
     */
    public function addLoaderFactory(
        callable $loaderFactory,
        callable $predicate
    ): Aggregate {

        return $this->loader->addLoaderFactory($loaderFactory, $predicate);
    }

    /**
     * @param mixed $loader
     * @param FileExtensionPredicate $predicate
     * @return void
     */
    private function maybeExtendLoader($loader, FileExtensionPredicate $predicate): void
    {
        switch (true) {
            case ($loader instanceof Loader):
                $this->loader->addLoader($loader, $predicate);
                break;
            case (is_callable($loader)):
                $this->loader->addLoaderFactory($loader, $predicate);
                break;
            case (
                is_string($loader)
                && class_exists($loader)
                && is_subclass_of($loader, Loader::class)
            ):
                $this->loader->addLoader(new $loader(), $predicate);
                break;
        }
    }
}
