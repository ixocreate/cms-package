<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Admin;

use Ixocreate\Cms\Site\Admin\Search\AdminCallableSearch;
use Ixocreate\Cms\Site\Admin\Search\AdminHandleSearch;
use Ixocreate\Cms\Site\Admin\Search\AdminMaxLevelSearch;
use Ixocreate\Cms\Site\Admin\Search\AdminMinLevelSearch;
use Ixocreate\Cms\Site\Admin\Search\AdminNavigationSearch;
use RecursiveIteratorIterator;

class AdminContainer implements AdminContainerInterface, \JsonSerializable
{
    /**
     * @var \ArrayIterator
     */
    private $iterator;

    /**
     * @var AdminItemFactory
     */
    private $itemFactory;

    /**
     * @var AdminSearchSubManager
     */
    private $searchSubManager;
    /**
     * @var AdminItem|null
     */
    private $parent;

    /**
     * Container constructor.
     * @param $structureItems
     * @param AdminSearchSubManager $searchSubManager
     * @param AdminItemFactory $itemFactory
     * @param AdminItem|null $parent
     */
    public function __construct($structureItems, AdminSearchSubManager $searchSubManager, AdminItemFactory $itemFactory, AdminItem $parent = null)
    {
        $this->iterator = new \ArrayIterator($structureItems);
        $this->itemFactory = $itemFactory;
        $this->searchSubManager = $searchSubManager;
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function current()
    {
        return $this->itemFactory->create($this->iterator->current(), $this->parent);
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
     * @return mixed|\RecursiveIterator
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
     * @return AdminContainerInterface
     */
    public function filter($filter, array $params = []): AdminContainerInterface
    {
        if (\is_callable($filter)) {
            $params['callable'] = $filter;
            $filter = AdminCallableSearch::class;
        }

        $items = [];
        /** @var AdminItem $item */
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
            /** @var AdminItem $child */
            foreach ($children as $child) {
                $enabledStructureItems[] = $child->structureItem();
            }

            $items[] = $item->structureItem()->withChildrenInfo($enabledStructureItems);
        }

        return new AdminContainer($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param callable $map
     * @param array $params
     * @return AdminContainerInterface
     */
    public function map(callable $map, array $params = []): AdminContainerInterface
    {
        $items = [];
        /** @var AdminItem $item */
        foreach ($this as $item) {

            $item = $map($item, $params);

            $children = $item->map($map, $params);

            $enabledStructureItems = [];
            /** @var AdminItem $child */
            foreach ($children as $child) {
                $enabledStructureItems[] = $child->structureItem();
            }

            $items[] = $item->structureItem()->withChildrenInfo($enabledStructureItems);
        }

        return new AdminContainer($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param int $level
     * @return AdminContainerInterface
     */
    public function withMaxLevel(int $level): AdminContainerInterface
    {
        return $this->filter(AdminMaxLevelSearch::class, ['level' => $level]);
    }

    /**
     * @param string $navigation
     * @return AdminContainerInterface
     */
    public function withNavigation(string $navigation): AdminContainerInterface
    {
        return $this->filter(AdminNavigationSearch::class, ['navigation' => $navigation]);
    }

    /**
     * @param callable|string $filter
     * @param array $params
     * @return AdminContainerInterface
     */
    public function where($filter, array $params = []): AdminContainerInterface
    {
        if (\is_callable($filter)) {
            $params['callable'] = $filter;
            $filter = AdminCallableSearch::class;
        }

        $items = [];
        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
        /** @var AdminItem $item */
        foreach ($iterator as $item) {
            try {
                if ($this->searchSubManager->get($filter)->search($item, $params) === true) {
                    $items[] = $item->structureItem();
                }
            } catch (\Exception $exception) {
            }
        }
        return new AdminContainer($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param int $level
     * @return AdminContainerInterface
     */
    public function withMinLevel(int $level): AdminContainerInterface
    {
        return $this->where(AdminMinLevelSearch::class, ['level' => $level]);
    }

    /**
     * @return AdminContainerInterface
     */
    public function flatten(): AdminContainerInterface
    {
        $items = [];
        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        /** @var AdminItem $item */
        foreach ($iterator as $item) {
            $items[] = $item->structureItem()->withChildrenInfo([]);
        }
        return new AdminContainer($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param callable|string $filter
     * @param array $params
     * @return AdminItem|null
     */
    public function find($filter, array $params = []): ?AdminItem
    {
        if (\is_callable($filter)) {
            $params['callable'] = $filter;
            $filter = AdminCallableSearch::class;
        }

        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        /** @var AdminItem $item */
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
     * @return AdminItem|null
     */
    public function findByHandle(string $handle): ?AdminItem
    {
        return $this->find(AdminHandleSearch::class, ['handle' => $handle]);
    }

    /**
     * @param callable $callable
     * @return AdminContainerInterface
     */
    public function sort(callable $callable): AdminContainerInterface
    {
        $array = \iterator_to_array($this);
        \uasort($array, $callable);

        $items = [];
        /** @var AdminItem $item */
        foreach ($array as $item) {
            $children = $item->sort($callable);

            $enabledStructureItems = [];
            /** @var AdminItem $child */
            foreach ($children as $child) {
                $enabledStructureItems[] = $child->structureItem();
            }

            $items[] = $item->structureItem()->withChildrenInfo($enabledStructureItems);
        }
        return new AdminContainer($items, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return AdminContainerInterface
     */
    public function paginate(int $limit, int $offset = 0): AdminContainerInterface
    {
        if ($offset > $this->count()) {
            return new AdminContainer([], $this->searchSubManager, $this->itemFactory);
        }

        $array = $this->iterator->getArrayCopy();
        $array = \array_slice($array, $offset, $limit);

        return new AdminContainer($array, $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $items = [];
        foreach ($this as $item) {
            $items[] = $item;
        }
        return $items;
    }

    /**
     * @param callable $callable
     * @return AdminItem|null
     */
    public function findOneBy(callable $callable): ?AdminItem
    {
        $iterator = new \RecursiveIteratorIterator($this, \RecursiveIteratorIterator::SELF_FIRST);
        /** @var AdminItem $item */
        foreach ($iterator as $item) {
            if ($callable($item) === true) {
                return $item;
            }
        }

        return null;
    }
}
