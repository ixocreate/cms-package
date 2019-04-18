<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Navigation;

use Ixocreate\Cms\Package\Entity\Navigation;
use Ixocreate\Cms\Package\Entity\Page;
use Ixocreate\Cms\Package\Entity\Sitemap;
use Ixocreate\Cms\Package\Repository\NavigationRepository;

final class Container implements \Iterator
{
    /**
     * @var Item[]
     */
    private $children;

    /**
     * @var NavigationRepository
     */
    private $navigationRepository;

    /**
     * Item constructor.
     * @param NavigationRepository $navigationRepository
     * @param array $children
     */
    public function __construct(NavigationRepository $navigationRepository, array $children)
    {
        $this->children = $children;
        $this->navigationRepository = $navigationRepository;
    }

    /**
     * @return Item[]
     */
    public function children(): array
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }

    public function asFlat(): Container
    {
        return new Container($this->navigationRepository, $this->asRecursiveFlat($this->children));
    }

    private function asRecursiveFlat(array $items): array
    {
        $newItems = [];
        foreach ($items as $item) {
            $newItems[] = $item;
            if ($item->hasChildren()) {
                $newItems = \array_merge($newItems, $this->asRecursiveFlat($item->children()));
            }
        }

        return $newItems;
    }

    public function withFilteredItems(callable $callable): Container
    {
        return new Container($this->navigationRepository, $this->recursiveFiltering($this->children, $callable));
    }

    private function recursiveFiltering(array $items, callable $callable): array
    {
        $collector = [];

        foreach ($items as $item) {
            if ($callable($item) !== true) {
                continue;
            }

            $collector[] = new Item(
                $item->page(),
                $item->sitemap(),
                $item->level(),
                $this->recursiveFiltering($item->children(), $callable),
                $item->isActive()
            );
        }

        return $collector;
    }

    /**
     * @param int $level
     * @return Container
     */
    public function withMinimumLevel(int $level): Container
    {
        return new Container($this->navigationRepository, $this->recursiveMinimum($this->children, [], $level));
    }

    /**
     * @param Item[] $items
     * @param array $collector
     * @param int $level
     * @return array
     */
    private function recursiveMinimum(array $items, array $collector, int $level): array
    {
        foreach ($items as $item) {
            if ($item->level() === $level) {
                $collector[] = $item;
                continue;
            }

            if ($item->level() < $level) {
                $collector = $this->recursiveMinimum($item->children(), $collector, $level);
            }
        }

        return $collector;
    }

    public function withMaximumLevel(int $level): Container
    {
        return new Container($this->navigationRepository, $this->recursiveMaximum($this->children(), $level));
    }

    /**
     * @param Item[] $items
     * @param array $collector
     * @param int $level
     * @return array
     */
    private function recursiveMaximum(array $items, int $level): array
    {
        $collector = [];
        foreach ($items as $item) {
            if ($item->level() > $level) {
                $collector[] = new Item($item->page(), $item->sitemap(), $item->level(), [], $item->isActive());
                continue;
            }

            $collector[] = new Item(
                $item->page(),
                $item->sitemap(),
                $item->level(),
                $this->recursiveMaximum($item->children(), $level),
                $item->isActive()
            );
        }

        return $collector;
    }

    public function withActiveState(Page $page): Container
    {
        $sitemap = null;
        /** @var Item $item */
        foreach ($this->asFlat() as $item) {
            if ($item->page()->id() === $page->id()) {
                $sitemap = $item->sitemap();
                break;
            }
        }

        if (empty($sitemap)) {
            return $this;
        }

        return new Container($this->navigationRepository, $this->recursiveActiveState($this->children(), $sitemap));
    }

    /**
     * @param Item[] $items
     * @param Page $page
     * @return Item[]
     */
    private function recursiveActiveState(array $items, Sitemap $sitemap): array
    {
        $collection = [];
        foreach ($items as $item) {
            $children = $this->recursiveActiveState($item->children(), $sitemap);

            if ((string)$sitemap->id() === (string) $item->sitemap()->id()) {
                $collection[] = new Item($item->page(), $item->sitemap(), $item->level(), $children, true);
                continue;
            }

            if ($sitemap->nestedLeft() > $item->sitemap()->nestedLeft() && $sitemap->nestedRight() < $item->sitemap()->nestedRight()) {
                $collection[] = new Item($item->page(), $item->sitemap(), $item->level(), $children, true);
                continue;
            }

            $collection[] = new Item($item->page(), $item->sitemap(), $item->level(), $children, false);
        }

        return $collection;
    }

    public function withOnlyActiveBranch(): ?Item
    {
        foreach ($this->children() as $item) {
            if ($item->isActive()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return Container
     */
    public function withNavigation(string $name): Container
    {
        $pageIds = [];
        $result = $navs = $this->navigationRepository->findBy(['navigation' => $name]);
        /** @var Navigation $navigationEntity */
        foreach ($result as $navigationEntity) {
            $pageIds[] = (string)$navigationEntity->pageId();
        }

        $container = new Container($this->navigationRepository, $this->recursiveNavigation($this->children, $pageIds));
        return $container;
    }

    /**
     * @param Item[] $items
     * @param array $pageIds
     * @return array
     */
    private function recursiveNavigation(array $items, array $pageIds): array
    {
        $collection = [];
        foreach ($items as $item) {
            if (!\in_array((string)$item->page()->id(), $pageIds)) {
                continue;
            }

            $children = $this->recursiveNavigation($item->children(), $pageIds);
            $collection[] = new Item($item->page(), $item->sitemap(), $item->level(), $children, $item->isActive());
        }

        return $collection;
    }

    public function withBreadcrumb(): Item
    {
        return $this->recursiveBreadCrumb($this->children());
    }

    /**
     * @param Item[] $items
     * @return Item
     */
    private function recursiveBreadCrumb(array $items): ?Item
    {
        foreach ($items as $item) {
            if (!$item->isActive()) {
                continue;
            }

            $child = $this->recursiveBreadCrumb($item->children());
            $child = (empty($child)) ? [] : [$child];
            return new Item($item->page(), $item->sitemap(), $item->level(), $child, $item->isActive());
        }

        return null;
    }

    /**
     * @return Item
     */
    public function current()
    {
        return \current($this->children);
    }

    /**
     *
     */
    public function next()
    {
        \next($this->children);
    }

    /**
     * @return int|mixed|null|string
     */
    public function key()
    {
        return \key($this->children);
    }

    /**
     * @return bool
     */
    public function valid()
    {
        $key = \key($this->children);
        return $key !== null && $key !== false;
    }

    /**
     *
     */
    public function rewind()
    {
        \reset($this->children);
    }

    public function __debugInfo()
    {
        return $this->children();
    }
}
