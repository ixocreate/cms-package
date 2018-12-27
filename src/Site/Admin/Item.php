<?php
namespace Ixocreate\Cms\Site\Admin;

use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Loader\PageLoaderInterface;
use Ixocreate\Cms\Loader\SitemapLoaderInterface;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Site\Structure\StructureItem;
use RecursiveIterator;

final class Item implements \JsonSerializable, \RecursiveIterator, \Countable
{
    /**
     * @var Item|null
     */
    private $parent;

    /**
     * @var SitemapLoaderInterface
     */
    private $sitemapLoader;

    /**
     * @var PageLoaderInterface
     */
    private $pageLoader;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var PageRoute
     */
    private $pageRoute;

    /**
     * @var StructureItem
     */
    private $structureItem;

    /**
     * @var array
     */
    private $pages;

    /**
     * @var PageTypeInterface
     */
    private $pageType;

    /**
     * @var Sitemap
     */
    private $sitemap;

    /**
     * @var Container
     */
    private $container;

    /**
     * Item constructor.
     * @param SitemapLoaderInterface $sitemapLoader
     * @param PageLoaderInterface $pageLoader
     * @param PageTypeSubManager $pageTypeSubManager
     * @param PageRoute $pageRoute
     * @param Item|null $parent
     * @param StructureItem $structureItem
     */
    public function __construct(
        SitemapLoaderInterface $sitemapLoader,
        PageLoaderInterface $pageLoader,
        PageTypeSubManager $pageTypeSubManager,
        PageRoute $pageRoute,
        StructureItem $structureItem,
        ?Item $parent = null
    ) {

        $this->parent = $parent;
        $this->sitemapLoader = $sitemapLoader;
        $this->pageLoader = $pageLoader;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageRoute = $pageRoute;
        $this->structureItem = $structureItem;

        $this->container = new Container(
            $sitemapLoader,
            $pageLoader,
            $pageTypeSubManager,
            $pageRoute,
            $structureItem->children(),
            $this
        );
    }

    public function __clone()
    {
        $this->container = clone $this->container;
        if (!empty($this->parent)) {
            $this->parent = clone $this->parent;
        }
    }

    /**
     * @return int
     */
    public function level(): int
    {
        return $this->structureItem->level();
    }

    /**
     * @return array
     */
    public function navigation(): array
    {
        return $this->structureItem->navigation();
    }

    /**
     * @return null|string
     */
    public function handle(): ?string
    {
        return $this->structureItem->handle();
    }

    /**
     * @return array
     */
    public function pages(): array
    {
        if ($this->pages === null) {
            $this->pages = [];

            foreach ($this->structureItem->pages() as $locale => $pageId) {
                $page = $this->pageLoader->receivePage($pageId);
                if (empty($page)) {
                    continue;
                }

                $this->pages[$page->locale()] = [
                    'page' => $page,
                    'url' => null,
                ];

                try {
                    $this->pages[$page->locale()]['url'] = $this->pageRoute->fromPage($page);
                } catch (\Exception $e) {

                }
            }

            $parent = $this->parent();
            foreach ($this->pages as $locale => $pageItem) {
                $this->pages[$locale]['isOnline'] = $pageItem['page']->isOnline();
                if ($this->pages[$locale]['isOnline'] === false) {
                    continue;
                }

                if (empty($parent)) {
                    continue;
                }

                $parentPages = $parent->pages();

                if (empty($parentPages[$locale])) {
                    $this->pages[$locale]['isOnline'] = false;
                    continue;
                }

                if ($parentPages[$locale]['isOnline'] === false) {
                    $this->pages[$locale]['isOnline'] = false;
                    continue;
                }
            }
        }

        return $this->pages;
    }

    /**
     * @return PageTypeInterface|null
     */
    public function pageType(): ?PageTypeInterface
    {
        if ($this->pageType === null) {
            $sitemap = $this->sitemap();
            if (empty($sitemap)) {
                return null;
            }

            $this->pageType = $this->pageTypeSubManager->get($sitemap->pageType());
        }

        return $this->pageType;
    }

    /**
     * @return Sitemap|null
     */
    public function sitemap(): ?Sitemap
    {
        if ($this->sitemap === null) {
            $this->sitemap = $this->sitemapLoader->receiveSitemap($this->structureItem->sitemapId());
        }

        return $this->sitemap;
    }

    /**
     * @return Item|null
     */
    public function parent(): ?Item
    {
        return $this->parent;
    }

    /**
     * @return Container
     */
    public function children(): Container
    {
        return $this->container;
    }

    public function childrenAllowed(): bool
    {
        return (!empty($this->pageTypeSubManager->allowedChildPageTypes($this->sitemapLoader->receiveHandles(), $this->pageType()::serviceName())));
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $pages = [];

        foreach ($this->pages() as $locale => $pageInfo) {
            $pages[$locale] = [
                'page' => $pageInfo['page']->toPublicArray(),
                'url' => $pageInfo['url'],
                'isOnline' => $pageInfo['isOnline'],
            ];
        }

        return [
            'sitemap' => $this->sitemap()->toPublicArray(),
            'pages' => $pages,
            'handle' => $this->handle(),
            'childrenAllowed' => $this->childrenAllowed(),
            'pageType' => [
                'handle' => $this->pageType()->handle(),
                'label' => $this->pageType()->label(),
                'allowedChildren' => $this->pageType()->allowedChildren(),
                'isRoot' => $this->pageType()->isRoot(),
                'name' => $this->pageType()::serviceName(),
                'terminal' => $this->pageType()->terminal(),
            ],
            'children' => $this->children()
        ];
    }

    /**
     * @param callable $callable
     * @return Item|null
     */
    public function findOneBy(callable $callable): ?Item
    {
        return $this->container->findOneBy($callable);
    }

    /**
     * @param callable $callable
     * @return Item
     */
    public function filter(callable $callable): Item
    {
        $container = $this->container->filter($callable);
        $item = clone $this;
        $item->container = $container;

        return $item;
    }

    /**
     * @return Item
     */
    public function current()
    {
        return $this->container->current();
    }

    /**
     * @return void
     */
    public function next()
    {
        $this->container->next();
    }

    /**
     * @return int
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
     * @return RecursiveIterator
     */
    public function getChildren()
    {
        return $this->container->getChildren();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->container->count();
    }
}
