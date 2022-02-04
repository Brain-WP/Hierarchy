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

use Brain\Hierarchy\Branch;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class Hierarchy
{
    public const FILTERABLE = 1;
    public const NOT_FILTERABLE = 2;

    private const BRANCHES = [
        'embed' => Branch\BranchEmbed::class,
        '404' => Branch\Branch404::class,
        'search' => Branch\BranchSearch::class,
        'frontpage' => Branch\BranchFrontPage::class,
        'home' => Branch\BranchHome::class,
        'post-type-archive' => Branch\BranchPostTypeArchive::class,
        'taxonomy' => Branch\BranchTaxonomy::class,
        'attachment' => Branch\BranchAttachment::class,
        'single' => Branch\BranchSingle::class,
        'page' => Branch\BranchPage::class,
        'singular' => Branch\BranchSingular::class,
        'category' => Branch\BranchCategory::class,
        'tag' => Branch\BranchTag::class,
        'author' => Branch\BranchAuthor::class,
        'date' => Branch\BranchDate::class,
        'archive' => Branch\BranchArchive::class,
        'paged' => Branch\BranchPaged::class,
    ];

    /**
     * @var int
     */
    private $flags;

    /**
     * @param int $flags
     */
    public function __construct(int $flags = self::FILTERABLE)
    {
        $this->flags = $flags;
    }

    /**
     * @param \WP_Query|null $query
     * @return array<string, list<string>>
     */
    public function hierarchy(?\WP_Query $query = null): array
    {
        return $this->parse($query)['hierarchy'];
    }

    /**
     * Get flatten hierarchy.
     *
     * @param \WP_Query|null $query
     * @return list<string>
     */
    public function templates(?\WP_Query $query = null): array
    {
        return $this->parse($query)['templates'];
    }

    /**
     * @deprecated Use Hierarchy::templates() instead.
     *
     * @param \WP_Query|null $query
     * @return array
     *
     * phpcs:disable Inpsyde.CodeQuality.NoAccessors
     */
    public function getTemplates(?\WP_Query $query = null): array
    {
        // phpcs:enable Inpsyde.CodeQuality.NoAccessors
        return $this->templates($query);
    }

    /**
     * @deprecated Use Hierarchy::hierarchy() instead.
     *
     * @param \WP_Query|null $query
     * @return array
     *
     * phpcs:disable Inpsyde.CodeQuality.NoAccessors
     */
    public function getHierarchy(?\WP_Query $query = null): array
    {
        // phpcs:enable Inpsyde.CodeQuality.NoAccessors
        return $this->hierarchy($query);
    }

    /**
     * Parse all branches.
     *
     * @param \WP_Query|null $query
     * @return array{
     *     hierarchy: array<string, list<string>>,
     *     templates: list<string>,
     *     query: \WP_Query|null
     * }
     */
    private function parse(?\WP_Query $query = null): array
    {
        global $wp_query;
        if (!$query && ($wp_query instanceof \WP_Query)) {
            $query = $wp_query;
        }

        $isFilterable = ($this->flags & self::FILTERABLE) === self::FILTERABLE;

        if (!$query) {
            $indexLeaves = $this->indexLeaves($isFilterable);
            $hierarchy = ['index' => $indexLeaves];
            $templates = $indexLeaves;

            return compact('hierarchy', 'templates', 'query');
        }

        $hierarchy = [];
        $templates = [];

        foreach ($this->allBranches($isFilterable) as $branch) {
            $branchObject = is_string($branch) ? new $branch() : $branch;
            if (!$branchObject->is($query)) {
                continue;
            }

            $name = $branchObject->name();
            if (isset($hierarchy[$name])) {
                continue;
            }

            $leaves = $this->parseBranch($branchObject, $query, $isFilterable);
            $hierarchy[$name] = $leaves;
            $templates = array_merge($templates, $leaves);
        }

        $hierarchy['index'] = $this->indexLeaves($isFilterable);
        $templates = array_values(array_unique(array_merge($templates, $hierarchy['index'])));

        return compact('hierarchy', 'templates', 'query');
    }

    /**
     * @param bool $isFilterable
     * @return list<string>
     */
    private function indexLeaves(bool $isFilterable): array
    {
        if (!$isFilterable) {
            return ['index'];
        }

        $leaves = $this->filterStringList('index_template_hierarchy', ['index']);
        in_array('index', $leaves, true) or $leaves[] = 'index';

        return $leaves;
    }

    /**
     * @return list<class-string<Branch\Branch>|Branch\Branch>
     */
    private function allBranches(bool $isFilterable): array
    {
        $default = array_values(self::BRANCHES);
        if (!$isFilterable) {
            return $default;
        }

        $filtered = apply_filters('brain.hierarchy.branches', $default);
        if (!is_array($filtered)) {
            return $default;
        }

        $parsed = [];
        $done = [];
        foreach ($filtered as $branch) {
            if (
                (is_string($branch) && is_subclass_of($branch, Branch\Branch::class, true))
                || ($branch instanceof Branch\Branch)
            ) {
                $key = is_string($branch) ? $branch : get_class($branch);
                ($done[$key] ?? false) or $parsed[] = $branch;
                $done[$key] = true;
            }
        }

        return $parsed;
    }

    /**
     * @param Branch\Branch $branch
     * @param \WP_Query $query
     * @param bool $filter
     * @return list<string>
     */
    private function parseBranch(Branch\Branch $branch, \WP_Query $query, bool $filter): array
    {
        $leaves = $branch->leaves($query);
        if (!$filter) {
            return $leaves;
        }

        return $this->filterStringList($branch->name() . '_template_hierarchy', $leaves);
    }

    /**
     * @param string $filter
     * @param list<string> $default
     * @return list<string>
     */
    private function filterStringList(string $filter, array $default): array
    {
        $filtered = apply_filters($filter, $default);

        if (!is_array($filtered)) {
            return $default;
        }

        $result = [];
        foreach ($filtered as $filteredItem) {
            (is_string($filteredItem) && $filteredItem) and $result[] = $filteredItem;
        }

        return $result ? array_values(array_unique($result)) : $default;
    }
}
