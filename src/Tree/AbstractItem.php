<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\Strategy\LoaderInterface;
use Ixocreate\Cms\Strategy\StructureInterface;
use Ixocreate\Collection\CollectionInterface;
use RecursiveIterator;

abstract class AbstractItem implements ItemInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var StructureInterface
     */
    private $structure;

    /**
     * @var TreeFactoryInterface
     */
    private $treeFactory;

    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var MutationCollection
     */
    private $mutationCollection;

    /**
     * AbstractItem constructor.
     * @param string $id
     * @param MutationCollection $mutationCollection
     * @param TreeFactoryInterface $treeFactory
     * @param LoaderInterface $loader
     */
    public function __construct(
        string $id,
        MutationCollection $mutationCollection,
        TreeFactoryInterface $treeFactory,
        LoaderInterface $loader
    ) {
        $this->id = $id;
        $this->mutationCollection = $mutationCollection;
        $this->treeFactory = $treeFactory;
        $this->loader = $loader;
    }

    /**
     * @return string
     */
    final public function id(): string
    {
        return $this->id;
    }

    /**
     * @return StructureInterface
     */
    private function structure(): StructureInterface
    {
        if ($this->structure === null) {
            $this->structure = $this->loader->get($this->id);
        }
        return $this->structure;
    }

    /**
     * @return ContainerInterface
     */
    private function container(): ContainerInterface
    {
        if ($this->container === null) {
            $this->container = $this->treeFactory->createContainer($this->structure()->children(), $this->mutationCollection);
        }
        return $this->container;
    }

    /**
     * @return ItemInterface|null
     */
    final public function parent(): ?ItemInterface
    {
        if (empty($this->structure->parent())) {
            return null;
        }

        return $this->treeFactory->createItem($this->structure->parent(), $this->mutationCollection);
    }

    /**
     * @return ContainerInterface
     */
    final public function below(): ContainerInterface
    {
        return $this->container();
    }

    /**
     * @return int
     */
    final public function level(): int
    {
        return $this->structure()->level();
    }

    /**
     * @return PageTypeInterface
     */
    final public function pageType(): PageTypeInterface
    {
        return $this->treeFactory->pageType($this->structure()->pageType());
    }

    /**
     * @return string|null
     */
    final public function handle(): ?string
    {
        return $this->structure()->handle();
    }

    /**
     * @param array $ids
     * @return ContainerInterface
     */
    final public function only(array $ids): ContainerInterface
    {
        return $this->container()->only($ids);
    }

    /**
     * @param callable|string $searchable
     * @param array $params
     * @return ContainerInterface
     */
    final public function filter($searchable, array $params = []): ContainerInterface
    {
        return $this->container()->filter($searchable, $params);
    }

    /**
     * @param callable|string $mutatable
     * @param array $params
     * @return ContainerInterface
     */
    final public function map($mutatable, array $params = []): ContainerInterface
    {
        return $this->container()->map($mutatable, $params);
    }

    /**
     * @param callable|string $searchable
     * @param array $params
     * @return ContainerInterface
     */
    final public function where($searchable, array $params = []): ContainerInterface
    {
        return $this->container()->where($searchable, $params);
    }

    /**
     * @return CollectionInterface
     */
    final public function flatten(): CollectionInterface
    {
        return $this->container()->flatten();
    }

    /**
     * @param callable|string $searchable
     * @param array $params
     * @return CollectionInterface
     */
    final public function find($searchable, array $params = []): CollectionInterface
    {
        return $this->container()->find($searchable, $params);
    }

    /**
     * @param callable|string $searchable
     * @param array $params
     * @return ItemInterface|null
     */
    final public function findOne($searchable, array $params = []): ?ItemInterface
    {
        return $this->container()->findOne($searchable, $params);
    }

    /**
     * @return ItemInterface
     */
    final public function current()
    {
        return $this->container()->current();
    }

    /**
     *
     */
    final public function next()
    {
        $this->container()->next();
    }

    /**
     * @return mixed
     */
    final public function key()
    {
        return $this->container()->key();
    }

    /**
     * @return bool
     */
    final public function valid()
    {
        return $this->container()->valid();
    }

    /**
     *
     */
    final public function rewind()
    {
        $this->container()->rewind();
    }

    /**
     * @return int
     */
    final public function count()
    {
        return $this->container()->count();
    }

    /**
     * @return bool
     */
    final public function hasChildren()
    {
        return $this->container()->hasChildren();
    }

    /**
     * @return RecursiveIterator
     */
    final public function getChildren()
    {
        return $this->container()->getChildren();
    }
}
