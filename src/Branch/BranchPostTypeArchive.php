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

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
final class BranchPostTypeArchive implements Branch
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'archive';
    }

    /**
     * @param \WP_Query $query
     * @return bool
     */
    public function is(\WP_Query $query): bool
    {
        return $query->is_post_type_archive() && $this->postType($query);
    }

    /**
     * @param \WP_Query $query
     * @return list<string>
     */
    public function leaves(\WP_Query $query): array
    {
        $type = $this->postType($query);

        return $type ? ["archive-{$type}", 'archive'] : ['archive'];
    }

    /**
     * @param \WP_Query $query
     * @return string|null
     */
    private function postType(\WP_Query $query): ?string
    {
        $type = $query->get('post_type');
        is_array($type) and $type = reset($type);
        if (!is_string($type) || ($type === '')) {
            return null;
        }

        $object = get_post_type_object($type);
        if (!($object instanceof \WP_Post_Type) || !$object->has_archive) {
            return null;
        }

        return $type;
    }
}
