<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Admin;

use Ixocreate\Cms\Tree\AbstractContainer;
use JsonSerializable;

final class Container extends AbstractContainer implements JsonSerializable
{
    public function jsonSerialize()
    {
        $items = [];
        foreach ($this as $item) {
            if (!$this->doFilter($item)) {
                continue;
            }
            $items[] = $item;
        }
        return $items;
    }
}
