<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\Tree\Filter\CallableFilter;
use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureItem;
use Ixocreate\Collection\Collection;
use Ixocreate\Collection\CollectionInterface;
use RecursiveIteratorIterator;

class AbstractContainer implements ContainerInterface
{
    /**
     * @var Structure
     */
    private $structure;

    /**
     * @var Structure
     */
    private $internalStructure;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var array
     */
    private $filter = [];
    /**
     * @var FilterManager
     */
    private $filterManager;

    public function __construct(Structure $structure, FactoryInterface $factory, FilterManager $filterManager, array $filter = [])
    {
        $this->structure = $structure;
        $this->factory = $factory;
        $this->filterManager = $filterManager;
        $this->filter = $filter;
    }

    final public function factory(): FactoryInterface
    {
        return $this->factory;
    }

    final public function structure(): Structure
    {
        if ($this->internalStructure === null && !empty($this->filter)) {
            $this->internalStructure = $this->structure->only(function (StructureItem $structureItem) {
                $item = $this->factory->createItem($structureItem);
                return $this->doFilter($item);
            });
        } elseif ($this->internalStructure === null && empty($this->filter)) {
            $this->internalStructure = $this->structure;
        }
        return $this->internalStructure;
    }

    /**
     * @return mixed|void
     */
    final public function current()
    {
        return $this->factory->createItem($this->structure()->current());
    }

    /**
     *
     */
    final public function next()
    {
        $this->structure()->next();
    }

    /**
     * @return mixed|void
     */
    final public function key()
    {
        return $this->structure()->key();
    }

    /**
     * @return bool|void
     */
    final public function valid()
    {
        return $this->structure()->valid();
    }

    /**
     *
     */
    final public function rewind()
    {
        $this->structure()->rewind();
    }

    /**
     * @return int|void
     */
    final public function count()
    {
        return $this->structure()->count();
    }

    /**
     * @return bool
     */
    final public function hasChildren()
    {
        return $this->count() > 0;
    }

    /**
     * @return mixed|\RecursiveIterator|void
     */
    final public function getChildren()
    {
        return $this->current();
    }

    private function normalizeFilter($filter, array $params = []): array
    {
        if (\is_callable($filter)) {
            $params['callable'] = $filter;
            $filter = CallableFilter::serviceName();
        }

        return [
            $filter,
            $params,
        ];
    }

    /**
     * @param string|callable $filter
     * @param array $params
     * @return ItemInterface|null
     */
    final public function find($filter, array $params = []): ?ItemInterface
    {
        list($filter, $params) = $this->normalizeFilter($filter, $params);
        $return = null;

        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $item) {
            if ($this->filterManager->get($filter)->filter($item, $params) === true && $this->doFilter($item)) {
                $return = $item;
                break;
            }
        }

        return $return;
    }

    /**
     * @param string|callable $filter
     * @param array $params
     * @return ContainerInterface
     */
    final public function where($filter, array $params = []): ContainerInterface
    {
        list($filter, $params) = $this->normalizeFilter($filter, $params);

        $item = $this->find($filter, $params);
        if (empty($item)) {
            return $this->factory->createContainer(
                $this->structure->only(function () {
                    return false;
                })
            );
        }

        $iterator = $this;
        if (($parent = $item->parent()) instanceof ItemInterface) {
            $iterator = $parent->below();
        }

        $sitemapIds = [];
        foreach ($iterator as $iteratorItem) {
            if ($this->filterManager->get($filter)->filter($iteratorItem, $params) === true && $this->doFilter($iteratorItem)) {
                $sitemapIds[$iteratorItem->structureItem()->sitemapId()] = true;
            }
        }
        return $this->factory->createContainer(
            $iterator->structure()->only(function (StructureItem $structureItem) use ($sitemapIds) {
                return isset($sitemapIds[$structureItem->sitemapId()]);
            })
        );
    }

    final public function flatten(): CollectionInterface
    {
        $items = [];
        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if ($this->doFilter($item) === false) {
                continue;
            }
            $items[$item->structureItem()->sitemapId()] = $item;
        }

        return new Collection($items);
    }

    /**
     * @param string|callable $filter
     * @param array $params
     * @return CollectionInterface
     */
    final public function search($filter, array $params = []): CollectionInterface
    {
        list($filter, $params) = $this->normalizeFilter($filter, $params);
        $items = [];

        $iterator = new \RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($iterator as $item) {
            if ($this->filterManager->get($filter)->filter($item, $params) === true && $this->doFilter($item)) {
                $items[$item->structureItem()->sitemapId()] = $item;
            }
        }

        return new Collection($items);
    }

    private function doFilter(ItemInterface $item): bool
    {
        foreach ($this->filter as $data) {
            list($filter, $params) = $data;
            if ($this->filterManager->get($filter)->filter($item, $params) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string|callable $filter
     * @param array $params
     * @return ContainerInterface
     */
    final public function filter($filter, array $params = []): ContainerInterface
    {
        $container = clone $this;
        $container->filter[] = $this->normalizeFilter($filter, $params);

        return $container;
    }
}
