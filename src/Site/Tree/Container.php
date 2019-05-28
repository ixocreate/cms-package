<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Tree;

use Ixocreate\Cms\Site\Tree\Search\CallableSearch;
use Ixocreate\Cms\Site\Tree\Search\HandleSearch;
use Ixocreate\Cms\Site\Tree\Search\MaxLevelSearch;
use Ixocreate\Cms\Site\Tree\Search\MinLevelSearch;
use Ixocreate\Cms\Site\Tree\Search\NavigationSearch;
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
     * @var SearchSubManager
     */
    private $searchSubManager;

    /**
     * Container constructor.
     * @param $structureItems
     * @param SearchSubManager $searchSubManager
     * @param ItemFactory $itemFactory
     */
    public function __construct($structureItems, SearchSubManager $searchSubManager, ItemFactory $itemFactory)
    {
        $this->iterator = new \ArrayIterator($structureItems);
        $this->itemFactory = $itemFactory;
        $this->searchSubManager = $searchSubManager;
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
        return $this->count() > 0;
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
     * @param callable|string $filter
     * @param array $params
     * @return ContainerInterface
     */
    public function filter($filter, array $params = []): ContainerInterface
    {
        if (\is_callable($filter)) {
            $params['callable'] = $filter;
            $filter = CallableSearch::class;
        }

        $items = [];
        /** @var Item $item */
        foreach ($this as $item) {
            try {
                if ($this->searchSubManager->get($filter)->search($item, $params) !== true) {
                    continue;
                }
            } catch (\Exception $exception) {
                continue;
            }


            $children = $item->filter($filter, $params);

            $enabledStructureItems = [];
            /** @var Item $child */
            foreach ($children as $child) {
                $enabledStructureItems[] = $child->structureItem();
            }

            $items[] = $item->structureItem()->withChildrenInfo($enabledStructureItems);
        }

        return new Container($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMaxLevel(int $level): ContainerInterface
    {
        return $this->filter(MaxLevelSearch::class, ['level' => $level]);
    }

    /**
     * @param string $navigation
     * @return ContainerInterface
     */
    public function withNavigation(string $navigation): ContainerInterface
    {
        return $this->filter(NavigationSearch::class, ['navigation' => $navigation]);
    }

    /**
     * @param callable|string $filter
     * @param array $params
     * @return ContainerInterface
     */
    public function where($filter, array $params = []): ContainerInterface
    {
        if (\is_callable($filter)) {
            $params['callable'] = $filter;
            $filter = CallableSearch::class;
        }

        $items = [];
        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
        /** @var Item $item */
        foreach ($iterator as $item) {
            try {
                if ($this->searchSubManager->get($filter)->search($item, $params) === true) {
                    $items[] = $item->structureItem();
                }
            } catch (\Exception $exception) {
            }
        }

        return new Container($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMinLevel(int $level): ContainerInterface
    {
        return $this->where(MinLevelSearch::class, ['level' => $level]);
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

        return new Container($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param callable|string $filter
     * @param array $params
     * @return Item|null
     */
    public function find($filter, array $params = []): ?Item
    {
        if (\is_callable($filter)) {
            $params['callable'] = $filter;
            $filter = CallableSearch::class;
        }

        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        /** @var Item $item */
        foreach ($iterator as $item) {
            try {
                if ($this->searchSubManager->get($filter)->search($item, $params) === true) {
                    return $this->itemFactory->create($item->structureItem());
                }
            } catch (\Exception $exception) {
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
        return $this->find(HandleSearch::class, ['handle' => $handle]);
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function sort(callable $callable): ContainerInterface
    {
        $array = \iterator_to_array($this);
        \uasort($array, $callable);

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

        return new Container($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return ContainerInterface
     */
    public function paginate(int $limit, int $offset = 0): ContainerInterface
    {
        if ($offset > $this->count()) {
            return new Container([], $this->searchSubManager, $this->itemFactory);
        }

        $array = $this->iterator->getArrayCopy();
        $array = \array_slice($array, $offset, $limit);

        return new Container($array, $this->searchSubManager, $this->itemFactory);
    }

    public function __debugInfo()
    {
        return [
            'items' => $this->iterator->getArrayCopy(),
        ];
    }
}
