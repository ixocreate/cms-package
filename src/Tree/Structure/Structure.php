<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Structure;

use Countable;
use Iterator;

final class Structure implements Iterator, Countable
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var int
     */
    private $level;

    /**
     * @var StructureItem|null
     */
    private $parent;

    /**
     * @var int
     */
    private $key = 0;

    /**
     * @var StructureStore
     */
    private $structureStore;

    public function __construct(StructureStore $structureStore, array $items, int $level, ?StructureItem $parent = null)
    {
        $this->structureStore = $structureStore;
        $this->items = $items;
        $this->level = $level;
        $this->parent = $parent;
    }

    public function current()
    {
        return new StructureItem($this->structureStore, $this->items[$this->key], $this->level, $this->parent);
    }

    public function next()
    {
        $this->key++;
    }

    public function key()
    {
        return $this->key;
    }

    public function valid()
    {
        return isset($this->items[$this->key]);
    }

    public function rewind()
    {
        $this->key = 0;
    }

    public function count()
    {
        return \count($this->items);
    }

    public function only(callable $callable): Structure
    {
        $items = [];
        /** @var StructureItem $item */
        foreach ($this as $item) {
            if ($callable($item) !== true) {
                continue;
            }

            $items[] = $item->structureKey();
        }

        return new Structure($this->structureStore, $items, $this->level, $this->parent);
    }

    public function level(): int
    {
        return $this->level;
    }

    public function parent(): ?StructureItem
    {
        return $this->parent;
    }

    /**
     * @return StructureStore
     */
    public function structureStore(): StructureStore
    {
        return $this->structureStore;
    }
}
