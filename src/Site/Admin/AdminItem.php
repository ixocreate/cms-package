<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Admin;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Loader\DatabasePageLoader;
use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Router\PageRoute;
use Ixocreate\Cms\Site\Structure\StructureItem;
use Ixocreate\ServiceManager\SubManager\SubManagerInterface;
use RecursiveIterator;

class AdminItem implements AdminContainerInterface, \JsonSerializable
{
    /**
     * @var array
     */
    private $pages;

    /**
     * @var Sitemap
     */
    private $sitemap;

    /**
     * @var StructureItem
     */
    private $structureItem;

    /**
     * @var AdminContainerInterface
     */
    private $container;

    /**
     * @var AdminItemFactory
     */
    private $itemFactory;

    /**
     * @var DatabasePageLoader
     */
    private $pageLoader;

    /**
     * @var DatabaseSitemapLoader
     */
    private $sitemapLoader;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var AdminSearchSubManager
     */
    private $searchSubManager;

    /**
     * @var PageRoute
     */
    private $pageRoute;

    /**
     * AdminItem constructor.
     * @param StructureItem $structureItem
     * @param AdminItemFactory $itemFactory
     * @param DatabasePageLoader $pageLoader
     * @param DatabaseSitemapLoader $sitemapLoader
     * @param SubManagerInterface $pageTypeSubManager
     * @param SubManagerInterface $searchSubManager
     * @param PageRoute $pageRoute
     */
    public function __construct(
        StructureItem $structureItem,
        AdminItemFactory $itemFactory,
        DatabasePageLoader $pageLoader,
        DatabaseSitemapLoader $sitemapLoader,
        SubManagerInterface $pageTypeSubManager,
        SubManagerInterface $searchSubManager,
        PageRoute $pageRoute
    ) {
        $this->structureItem = clone $structureItem;
        $this->itemFactory = $itemFactory;
        $this->pageLoader = $pageLoader;
        $this->sitemapLoader = $sitemapLoader;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->searchSubManager = $searchSubManager;
        $this->pageRoute = $pageRoute;


        $this->container = new AdminContainer( $this->structureItem->children(), $this->searchSubManager, $this->itemFactory);
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
     * @return AdminContainerInterface
     */
    public function below(): AdminContainerInterface
    {
        return new AdminContainer($this->structureItem->children(), $this->searchSubManager, $this->itemFactory);
    }

    /**
     * @return PageTypeInterface
     */
    public function pageType(): PageTypeInterface
    {
        return $this->pageTypeSubManager->get($this->sitemap()->pageType());
    }

    /**
     * @param string $locale
     * @return Page
     * @throws \Exception
     */
    public function page(string $locale): Page
    {
        if (!\array_key_exists($locale, $this->structureItem()->pages())) {
            throw new \Exception(\sprintf("Page with locale '%s' does not exists", $locale));
        }

        return
            $this->pageLoader->receivePage($this->structureItem()->pages()[$locale]);
    }

    /**
     * @param string $locale
     * @param array $params
     * @return string
     */
    public function pageUrl(string $locale, array $params = []): string
    {
        try {
            return $this->pageRoute->fromPage($this->page($locale, $params));
        } catch (\Exception $exception) {
        }

        return "";
    }

    /**
     * @return Sitemap
     */
    public function sitemap(): Sitemap
    {
        return $this->sitemapLoader->receiveSitemap($this->structureItem()->sitemapId());
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
     * @param Sitemap|null $currentSitemap
     * @return bool
     */
    public function isActive(?Sitemap $currentSitemap = null): bool
    {
        if (empty($currentSitemap)) {
            return false;
        }

        if ((string) $this->sitemap()->id() === (string) $currentSitemap->id()) {
            return true;
        }

        if ($currentSitemap->nestedLeft() > $this->sitemap()->nestedLeft() && $currentSitemap->nestedRight() < $this->sitemap()->nestedRight()) {
            return true;
        }

        return false;
    }

    /**
     * @param callable|string $filter
     * @param array $params
     * @return AdminContainerInterface
     */
    public function filter($filter, array $params = []): AdminContainerInterface
    {
        return $this->container->filter($filter, $params);
    }

    /**
     * @param int $level
     * @return AdminContainerInterface
     */
    public function withMaxLevel(int $level): AdminContainerInterface
    {
        return $this->container->withMaxLevel($level);
    }

    /**
     * @param string $navigation
     * @return AdminContainerInterface
     */
    public function withNavigation(string $navigation): AdminContainerInterface
    {
        return $this->container->withNavigation($navigation);
    }

    /**
     * @param callable|string $filter
     * @param array $params
     * @return AdminContainerInterface
     */
    public function where($filter, array $params = []): AdminContainerInterface
    {
        return $this->container->where($filter, $params);
    }

    /**
     * @param int $level
     * @return AdminContainerInterface
     */
    public function withMinLevel(int $level): AdminContainerInterface
    {
        return $this->container->withMinLevel($level);
    }

    /**
     * @return AdminContainerInterface
     */
    public function flatten(): AdminContainerInterface
    {
        return $this->container->flatten();
    }

    /**
     * @param callable|string $filter
     * @param array $params
     * @return AdminItem|null
     */
    public function find($filter, array $params = []): ?AdminItem
    {
        return $this->container->find($filter, $params);
    }

    /**
     * @param string $handle
     * @return AdminItem|null
     */
    public function findByHandle(string $handle): ?AdminItem
    {
        return $this->container->findByHandle($handle);
    }

    /**
     * @param callable $callable
     * @return AdminContainerInterface
     */
    public function sort(callable $callable): AdminContainerInterface
    {
        return $this->container->sort($callable);
    }

    /**
     * @return AdminItem
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
     * @return Sitemap|null
     */
    public function sitemaps()
    {
            $this->sitemap = $this->structureItem->sitemapId();
    }

    /**
     * @return RecursiveIterator|void
     */
    public function getChildren()
    {
        return $this->container->getChildren();
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
                'label' => $this->pageType()->label(),
                'allowedChildren' => $this->pageType()->allowedChildren(),
                'isRoot' => $this->pageType() instanceof RootPageTypeInterface,
                'name' => $this->pageType()::serviceName(),
                'terminal' => $this->pageType() instanceof TerminalPageTypeInterface,
            ],
            'children' => $this->container,
        ];
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

            foreach ($this->pages as $locale => $pageItem) {
                $this->pages[$locale]['isOnline'] = $pageItem['page']->isOnline();
                if ($this->pages[$locale]['isOnline'] === false) {
                    continue;
                }

                if (empty($parent)) {
                    continue;
                }

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

    public function childrenAllowed(): bool
    {
        return !empty($this->pageTypeSubManager->allowedPageTypes($this->sitemapLoader->receiveHandles(), $this->pageType()::serviceName()));
    }

    /**
     * @return AdminContainer
     */
    public function children(): AdminContainer
    {
        return $this->container;
    }
}
