<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use ArrayIterator;
use Closure;
use Ixocreate\Cms\Strategy\LoaderInterface;
use Ixocreate\Cms\Tree\Mutatable\CallbackMutatable;
use Ixocreate\Cms\Tree\Searchable\CallbackSearchable;
use Ixocreate\Collection\Collection;
use Ixocreate\Collection\CollectionInterface;
use RecursiveIteratorIterator;

abstract class AbstractContainer implements ContainerInterface
{
    /**
     * @var \ArrayIterator
     */
    private $iterator;

    /**
     * @var TreeFactoryInterface
     */
    private $treeFactory;
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var array
     */
    private $ids;
    /**
     * @var MutationCollection
     */
    private $mutationCollection;

    public function __construct(
        array $ids,
        MutationCollection $mutationCollection,
        TreeFactoryInterface $treeFactory,
        LoaderInterface $loader
    ) {
        $this->ids = $ids;
        $this->mutationCollection = $mutationCollection;
        $this->treeFactory = $treeFactory;
        $this->loader = $loader;
    }

    private function search($searchable, array $params, ItemInterface $item): bool
    {
        $searchable = $this->treeFactory->searchable($searchable);

        return $searchable->search($item, $params);
    }

    private function mutate($mutatable, array $params, ItemInterface $item): ItemInterface
    {
        $mutatable = $this->treeFactory->mutatable($mutatable);

        return $mutatable->mutate($item, $params);
    }

    /**
     * @return ArrayIterator
     */
    private function iterator(): ArrayIterator
    {
        if ($this->iterator === null) {
            if ($this->mutationCollection->hasFilter()) {
                $ids = [];
                foreach ($this->ids as $id) {
                    $item = $this->treeFactory->createItem($id, $this->mutationCollection);
                    foreach ($this->mutationCollection->filter() as $filter) {
                        if ($this->search($filter[0],$filter[1], $item) === false) {
                            break 2;
                        }
                    }

                    $ids[] = $id;
                }
                $this->ids = $ids;
            }

            $this->iterator = new ArrayIterator($this->ids);
        }

        return $this->iterator;
    }

    public function only(array $ids): ContainerInterface
    {
        $ids = \array_values(\array_intersect($ids, $this->ids));
        return $this->treeFactory->createContainer($ids, $this->mutationCollection);
    }

    /**
     * @param callable|string $searchable
     * @param array $params
     * @return ContainerInterface
     */
    final public function filter($searchable, array $params = []): ContainerInterface
    {
        if ($searchable instanceof Closure) {
            $params['callback'] = $searchable;
            $searchable = CallbackSearchable::class;
        }

        $mutationCollection = $this->mutationCollection->addFilter($searchable, $params);

        return $this->treeFactory->createContainer($this->ids, $mutationCollection);
    }

    /**
     * @param callable|string $mutatable
     * @param array $params
     * @return ContainerInterface
     */
    final public function map($mutatable, array $params = []): ContainerInterface
    {
        if ($mutatable instanceof Closure) {
            $params['callback'] = $mutatable;
            $mutatable = CallbackMutatable::class;
        }

        $mutationCollection = $this->mutationCollection->addMap($mutatable, $params);

        return $this->treeFactory->createContainer($this->ids, $mutationCollection);
    }

    /**
     * @param callable|string $searchable
     * @param array $params
     * @return ContainerInterface
     */
    final public function where($searchable, array $params = []): ContainerInterface
    {
        if ($searchable instanceof Closure) {
            $params['callback'] = $searchable;
            $searchable = CallbackSearchable::class;
        }

        $item = $this->findOne($searchable, $params);
        if (empty($item)) {
            return $this->treeFactory->createContainer([], $this->mutationCollection);
        }

        $parent = $item->parent();
        if (empty($parent)) {
            return $this->treeFactory->createRoot()->filter($searchable, $params);
        }

        return $parent->below()->filter($searchable, $params);
    }

    /**
     * @return CollectionInterface
     */
    final public function flatten(): CollectionInterface
    {
        return new Collection(function () {
            $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
            /** @var ItemInterface $item */
            foreach ($iterator as $item) {
                yield $item;
            }
        });
    }

    /**
     * @param callable|string $searchable
     * @param array $params
     * @return CollectionInterface
     */
    final public function find($searchable, array $params = []): CollectionInterface
    {
        if ($searchable instanceof Closure) {
            $params['callback'] = $searchable;
            $searchable = CallbackSearchable::class;
        }

        return new Collection(function () use ($searchable, $params){
            $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
            /** @var ItemInterface $item */
            foreach ($iterator as $item) {
                if ($this->search($searchable, $params, $item) === true) {
                    yield $item;
                }
            }
        });
    }

    /**
     * @param callable|string $searchable
     * @param array $params
     * @return ItemInterface|null
     */
    final public function findOne($searchable, array $params = []): ?ItemInterface
    {
        if ($searchable instanceof Closure) {
            $params['callback'] = $searchable;
            $searchable = CallbackSearchable::class;
        }

        $foundItem = null;

        $iterator = new RecursiveIteratorIterator($this, RecursiveIteratorIterator::SELF_FIRST);
        /** @var ItemInterface $item */
        foreach ($iterator as $item) {
            if ($this->search($searchable, $params, $item) === true) {
                $foundItem = $item;
                break;
            }
        }

        return $foundItem;
    }

    /**
     * @return ItemInterface|mixed
     */
    final public function current()
    {
        $item = $this->treeFactory->createItem($this->iterator()->current(), $this->mutationCollection);

        if ($this->mutationCollection->hasMap() === true) {
            foreach ($this->mutationCollection->map() as $map) {
                $item = $this->mutate($map[0], $map[1], $item);
            }
        }

        return $item;
    }

    /**
     *
     */
    final public function next()
    {
        $this->iterator()->next();
    }

    /**
     *
     */
    final public function key()
    {
        return $this->iterator()->key();
    }

    /**
     *
     */
    final public function valid()
    {
        return $this->iterator()->valid();
    }

    /**
     *
     */
    final public function rewind()
    {
        $this->iterator()->rewind();
    }

    /**
     *
     */
    final public function count()
    {
        return $this->iterator()->count();
    }

    /**
     *
     */
    final public function hasChildren()
    {
        return $this->count() > 0;
    }

    /**
     *
     */
    final public function getChildren()
    {
        return $this->current();
    }
}
