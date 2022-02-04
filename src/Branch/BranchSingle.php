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
final class BranchSingle implements Branch
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
        return 'single';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return $query->is_single();
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        $post = $query->get_queried_object();
        if (!($post instanceof \WP_Post) || !$post->ID) {
            return ['single'];
        }

        $leaves = [
            "single-{$post->post_type}-{$post->post_name}",
            "single-{$post->post_type}",
            'single',
        ];

        $decoded = urldecode($post->post_name);
        if ($decoded !== $post->post_name) {
            array_unshift($leaves, "single-{$post->post_type}-{$decoded}");
        }

        $template = $this->postTemplates->findFor($post);
        $template and array_unshift($leaves, $template);

        return $leaves;
    }
}
