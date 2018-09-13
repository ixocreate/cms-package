<?php
namespace KiwiSuite\Cms\Site\Admin;

use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Loader\PageLoaderInterface;
use KiwiSuite\Cms\Loader\SitemapLoaderInterface;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Router\PageRoute;

final class Item implements \JsonSerializable
{
    /**
     * @var null|string
     */
    private $sitemapId;

    /**
     * @var array|null
     */
    private $pages;

    /**
     * @var array|null
     */
    private $navigation;

    /**
     * @var array|null
     */
    private $children;

    /**
     * @var int
     */
    private $level;

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
     * @var null|string
     */
    private $handle;

    /**
     * Item constructor.
     * @param SitemapLoaderInterface $sitemapLoader
     * @param PageLoaderInterface $pageLoader
     * @param PageTypeSubManager $pageTypeSubManager
     * @param PageRoute $pageRoute
     * @param int $level
     * @param Item|null $parent
     * @param null|string $sitemapId
     * @param null|string $handle
     * @param array|null $pages
     * @param array|null $navigation
     * @param array|null $children
     */
    public function __construct(
        SitemapLoaderInterface $sitemapLoader,
        PageLoaderInterface $pageLoader,
        PageTypeSubManager $pageTypeSubManager,
        PageRoute $pageRoute,
        int $level,
        ?Item $parent,
        ?string $sitemapId,
        ?string $handle,
        ?array $pages,
        ?array $navigation,
        ?array $children
    ) {

        $this->sitemapId = $sitemapId;
        $this->pages = $pages;
        $this->navigation = $navigation;
        $this->children = $children;
        $this->level = $level;
        $this->parent = $parent;
        $this->sitemapLoader = $sitemapLoader;
        $this->pageLoader = $pageLoader;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageRoute = $pageRoute;
        $this->handle = $handle;
    }

    /**
     * @return int
     */
    public function level(): int
    {
        return $this->level;
    }

    /**
     * @return null|string
     */
    public function handle(): ?string
    {
        return $this->handle;
    }

    /**
     * @return array
     */
    public function pages(): array
    {
        $pages = [];

        foreach ($this->pages as $locale => $pageId) {
            $page = $this->pageLoader->receivePage($pageId);
            if (empty($page)) {
                continue;
            }

            $pages[$page->locale()] = [
                'page' => $page,
                'url' => null,
            ];

            try {
                $pages[$page->locale()]['url'] = $this->pageRoute->fromPage($page);
            } catch (\Exception $e) {

            }
        }

        $parent = $this->parent();
        foreach ($pages as $locale => $pageItem) {
            $pages[$locale]['isOnline'] = $pageItem['page']->isOnline();
            if ($pages[$locale]['isOnline'] === false) {
                continue;
            }

            if (empty($parent)) {
                continue;
            }

            $parentPages = $parent->pages();

            if (empty($parentPages[$locale])) {
                $pages[$locale]['isOnline'] = false;
                continue;
            }

            if ($parentPages[$locale]['isOnline'] === false) {
                $pages[$locale]['isOnline'] = false;
                continue;
            }
        }

        return $pages;
    }

    /**
     * @return PageTypeInterface|null
     */
    public function pageType(): ?PageTypeInterface
    {
        $sitemap = $this->sitemap();
        if (empty($sitemap)) {
            return null;
        }

        return $this->pageTypeSubManager->get($sitemap->pageType());
    }

    /**
     * @return Sitemap|null
     */
    public function sitemap(): ?Sitemap
    {
        return $this->sitemapLoader->receiveSitemap($this->sitemapId);
    }

    /**
     * @return Item|null
     */
    public function parent(): ?Item
    {
        return $this->parent;
    }

    /**
     * @return ItemCollection
     */
    public function children(): ItemCollection
    {
        $items = [];

        foreach ($this->children as $child) {
            $items[] = new Item(
                $this->sitemapLoader,
                $this->pageLoader,
                $this->pageTypeSubManager,
                $this->pageRoute,
                $this->level + 1,
                $this,
                $child['sitemapId'],
                $child['handle'],
                $child['pages'],
                $child['navigation'],
                $child['children']
            );
        }

        return new ItemCollection($items);
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
            ],
            'children' => $this->children()
        ];
    }
}