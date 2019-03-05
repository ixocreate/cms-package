<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Site\Tree;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\Site\Structure\StructureItem;
use RecursiveIterator;

class Item implements ContainerInterface
{
    /**
     * @var StructureItem
     */
    private $structureItem;

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * Item constructor.
     * @param StructureItem $structureItem
     * @param ItemFactory $itemFactory
     */
    public function __construct(
        StructureItem $structureItem,
        ItemFactory $itemFactory
    ) {
        $this->structureItem = clone $structureItem;
        $this->itemFactory = $itemFactory;

        $this->container = new Container($this->structureItem->children(), $this->itemFactory);

    }

    public function count()
    {
        return $this->container->count();
    }

    public function structureItem(): StructureItem
    {
        return $this->structureItem;
    }

    /**
     * @return ContainerInterface
     */
    public function below(): ContainerInterface
    {
        return new Container($this->structureItem->children(), $this->itemFactory);
    }

    /**
     * @return PageTypeInterface
     */
    public function pageType(): PageTypeInterface
    {

    }

    public function page(string $locale): Page
    {

    }

    public function sitemap(): Sitemap
    {

    }

    public function pageContent(string $locale)
    {

    }

    public function level(): int
    {
        return $this->structureItem()->level();
    }

    public function handle(): ?string
    {
        return $this->structureItem->handle();
    }

    public function navigation(): array
    {
        return $this->structureItem()->navigation();
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function filter(callable $callable): ContainerInterface
    {
        return $this->container->filter($callable);
    }

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMaxLevel(int $level): ContainerInterface
    {
        return $this->container->withMaxLevel($level);
    }

    /**
     * @param string $navigation
     * @return ContainerInterface
     */
    public function withNavigation(string $navigation): ContainerInterface
    {
        return $this->container->withNavigation($navigation);
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function where(callable $callable): ContainerInterface
    {
        return $this->container->where($callable);
    }

    /**
     * @param int $level
     * @return ContainerInterface
     */
    public function withMinLevel(int $level): ContainerInterface
    {
        return $this->container->withMinLevel($level);
    }

    /**
     * @return ContainerInterface
     */
    public function flatten(): ContainerInterface
    {
        return $this->container->flatten();
    }

    /**
     * @param callable $callable
     * @return Item|null
     */
    public function find(callable $callable): ?Item
    {
        return $this->container->find($callable);
    }

    /**
     * @param string $handle
     * @return Item|null
     */
    public function findByHandle(string $handle): ?Item
    {
        return $this->container->findByHandle($handle);
    }

    /**
     * @param callable $callable
     * @return ContainerInterface
     */
    public function sort(callable $callable): ContainerInterface
    {
        return $this->container->sort($callable);
    }

    /**
     * @return Item
     */
    public function current()
    {
        return $this->container->current();
    }

    /**
     *
     */
    public function next()
    {
        $this->container->next();
    }

    /**
     * @return mixed
     */
    public function key()
    {
        return $this->container->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->container->valid();
    }

    /**
     *
     */
    public function rewind()
    {
        $this->container->rewind();
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return $this->container->hasChildren();
    }

    /**
     * @return RecursiveIterator|void
     */
    public function getChildren()
    {
        return $this->container->getChildren();
    }
}
