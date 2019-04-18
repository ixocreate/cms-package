<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Tree\Search;

use Ixocreate\Cms\Site\Tree\Item;
use Ixocreate\Cms\Site\Tree\SearchInterface;

final class MaxLevelSearch implements SearchInterface
{
    /**
     * @param Item $item
     * @param array $params
     * @return bool
     */
    public function search(Item $item, array $params = []): bool
    {
        if (!\array_key_exists('level', $params)) {
            return false;
        }

        return $item->level() <= $params['level'];
    }
}
