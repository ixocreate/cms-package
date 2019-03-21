<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Site\Tree;


use RecursiveIteratorIterator;

class Container implements ContainerInterface
{
    /**
     * @var \ArrayIterator
     */
    private $iterator;
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * Container constructor.
     * @param $structureItems
     * @param ItemFactory $itemFactory
     */
    public function __construct($structureItems, ItemFactory $itemFactory)
    {
        $this->iterator = new \ArrayIterator($structureItems);
        $this->itemFactory = $itemFactory;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->itemFactory->create($this->iterator->current());
    }

    /**
     *
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->iterator->valid();
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return ($this->count() > 0);
    }

    /**
     * @return Item|\RecursiveIterator
     */
    public function getChildren()
    {
        return $this->current();
    }

    /**
     *
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->iterator->count();
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function filter(callable $callable): ContainerInterface
    {
        $items = [];
        /** @var Item $item */
        foreach ($this as $item) {
            if ($callable($item) !== true) {
                continue;
            }

            $children = $item->filter($callable);

            $enabledStructureItems = [];
            /** @var Item $child */
            foreach ($children as $child) {
                $enabledStructureItems[] = $child->structureItem();
            }

            $items[] = $item->structureItem()->withChildrenInfo($enabledStructureItems);
        }

        return new Container($items, $this->itemFactory);
    }

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMaxLevel(int $level): ContainerInterface
    {
        return $this->filter(function (Item $item) use ($level){
            return $item->level() <= $level;
        });
    }

    /**
     * @param string $navigation
     * @return ContainerInterface
     */
    public function withNavigation(string $navigation): ContainerInterface
    {
        return $this->filter(function (Item $item) use ($navigation) {
            return \in_array($navigation, $item->navigation());
        });
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function where(callable $callable): ContainerInterface
    {
        $items = [];
        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
        /** @var Item $item */
        foreach ($iterator as $item) {
            if ($callable($item) === true) {
                $items[] = $item->structureItem();
            }
        }

        return new Container($items, $this->itemFactory);
    }

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMinLevel(int $level): ContainerInterface
    {
        return $this->where(function (Item $item) use ($level){
           return $item->level() === $level;
        });
    }

    /**
     * @return ContainerInterface
     */
    public function flatten(): ContainerInterface
    {
        $items = [];
        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        /** @var Item $item */
        foreach ($iterator as $item) {
            $items[] = $item->structureItem()->withChildrenInfo([]);
        }

        return new Container($items, $this->itemFactory);
    }

    /**
     * @param callable $callable
     * @return Item|null
     */
    public function find(callable $callable): ?Item
    {
        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        /** @var Item $item */
        foreach ($iterator as $item) {
            if ($callable($item) === true) {
                return $this->itemFactory->create($item->structureItem());
            }
        }

        return null;
    }

    /**
     * @param string $handle
     * @return Item|null
     */
    public function findByHandle(string $handle): ?Item
    {
        return $this->find(function (Item $item) use ($handle) {
            return $item->handle() === $handle;
        });
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function sort(callable $callable): ContainerInterface
    {
        $array = iterator_to_array($this);
        uasort($array, $callable);

        $items = [];
        /** @var Item $item */
        foreach ($array as $item) {
            $children = $item->sort($callable);

            $enabledStructureItems = [];
            /** @var Item $child */
            foreach ($children as $child) {
                $enabledStructureItems[] = $child->structureItem();
            }

            $items[] = $item->structureItem()->withChildrenInfo($enabledStructureItems);

        }

        return new Container($items, $this->itemFactory);
    }
}
