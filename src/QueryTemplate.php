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

namespace Brain\Hierarchy;

use Brain\Hierarchy\Finder\ByFolders;
use Brain\Hierarchy\Finder\TemplateFinder;
use Brain\Hierarchy\Loader\FileRequire;
use Brain\Hierarchy\Loader\Loader;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class QueryTemplate
{
    /**
     * @var TemplateFinder
     */
    protected $finder;

    /**
     * @var Loader
     */
    protected $loader;

    /**
     * @return bool
     */
    public static function mainQueryTemplateAllowed(): bool
    {
        return
            (
                (($_SERVER['REQUEST_METHOD'] ?? '') !== 'HEAD') // phpcs:ignore
                || !apply_filters('exit_on_http_head', true)
            )
            && !is_robots()
            && !is_favicon()
            && !is_feed()
            && !is_trackback()
            && !is_embed();
    }

    /**
     * @param TemplateFinder|null $finder
     * @param Loader|null $loader
     */
    public function __construct(
        ?TemplateFinder $finder = null,
        ?Loader $loader = null
    ) {
        // if no finder provided, let's use the one that simulates core behaviour
        $this->finder = $finder ?: new ByFolders();
        $this->loader = $loader ?: new FileRequire();
    }

    /**
     * Find a template for the given WP_Query.
     * If no WP_Query provided, global \WP_Query is used.
     * By default, found template passes through "{$type}_template" filter.
     *
     * @param \WP_Query|null $query
     * @param bool $filters
     * @return string
     */
    public function findTemplate(?\WP_Query $query = null, bool $filters = true): string
    {
        $leaves = (new Hierarchy())->hierarchy($query);
        if (!$leaves) {
            return '';
        }

        $types = array_keys($leaves);
        $found = '';
        while ($types && !$found) {
            $type = array_shift($types);
            $found = $this->finder->findFirst($leaves[$type], (string) $type);
            $filters and $found = $this->applyFilter("{$type}_template", $found, $query);
        }

        return $found;
    }

    /**
     * Find a template for the given query and load it.
     * If no WP_Query provided, global \WP_Query is used.
     * By default, found template passes through "{$type}_template" and "template_include" filters.
     *
     * @param \WP_Query|null $query
     * @param bool $filters
     * @param bool $found
     * @return string
     */
    public function loadTemplate(
        ?\WP_Query $query = null,
        bool $filters = true,
        bool &$found = false
    ): string {

        $template = $this->findTemplate($query, $filters);
        $filters and $template = $this->applyFilter('template_include', $template, $query);
        $found = is_file($template) && is_readable($template);

        return $found ? $this->loader->load($template) : '';
    }

    /**
     * To maximize compatibility, when applying a filters and the WP_Query object we are using is
     * NOT the main query, we temporarily set global $wp_query + $wp_the_query to the custom query.
     *
     * @param string $filter
     * @param string $value
     * @param \WP_Query|null $query
     * @return string
     */
    protected function applyFilter(string $filter, string $value, ?\WP_Query $query = null): string
    {
        /** @var array{\WP_Query, \WP_Query}|null $backup */
        $backup = null;
        global $wp_query, $wp_the_query;
        /**
         * @var \WP_Query $wp_query
         * @var \WP_Query $wp_the_query
         */
        is_null($query) and $query = $wp_query;
        $custom = !$query->is_main_query();

        if ($custom && ($wp_query instanceof \WP_Query) && ($wp_the_query instanceof \WP_Query)) {
            $backup = [$wp_query, $wp_the_query];
            $wp_query = $query;
            $wp_the_query = $query;
        }

        $filtered = apply_filters($filter, $value);
        is_string($filtered) and $value = $filtered;

        if ($custom && $backup) {
            [$wpQuery, $wpTheQuery] = $backup;
            $wp_query = $wpQuery;
            $wp_the_query = $wpTheQuery;
        }

        return $value;
    }
}
