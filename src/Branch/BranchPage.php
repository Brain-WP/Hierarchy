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

namespace Brain\Hierarchy\Branch;

use Brain\Hierarchy\PostTemplates;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class BranchPage implements Branch
{
    /**
     * @var PostTemplates
     */
    private $postTemplates;

    /**
     * @param PostTemplates|null $postTemplates
     */
    public function __construct(?PostTemplates $postTemplates = null)
    {
        $this->postTemplates = $postTemplates ?: new PostTemplates();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'page';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return $query->is_page();
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        /** @var \WP_Post $post */
        $post = $query->get_queried_object();
        $post instanceof \WP_Post or $post = new \WP_Post((object)['ID' => 0]);

        $template = $this->postTemplates->findFor($post);
        $pagename = $query->get('pagename');
        (!$pagename && $post->ID) and $pagename = $post->post_name;

        $leaves = $template ? [$template] : [];
        $baseLeaves = $post->ID ? ["page-{$post->ID}", 'page'] : ['page'];

        if (!$pagename || !is_string($pagename)) {
            return array_merge($leaves, $baseLeaves);
        }

        $pagenameDecoded = urldecode($pagename);
        if ($pagenameDecoded !== $pagename) {
            $leaves[] = "page-{$pagenameDecoded}";
        }

        $leaves[] = "page-{$pagename}";

        return array_merge($leaves, $baseLeaves);
    }
}
