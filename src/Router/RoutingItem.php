<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Action\Frontend\RenderAction;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Middleware\LoadPageContentMiddleware;
use Ixocreate\Cms\Middleware\LoadPageMiddleware;
use Ixocreate\Cms\Middleware\LoadPageTypeMiddleware;
use Ixocreate\Cms\Middleware\LoadSitemapMiddleware;
use Ixocreate\Cms\PageType\MiddlewarePageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\RoutingAwareInterface;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Site\Structure\StructureItem;
use Ixocreate\Intl\LocaleManager;
use Ixocreate\ServiceManager\SubManager\SubManagerInterface;

final class RoutingItem implements \RecursiveIterator, \Countable
{
    /**
     * @var StructureItem
     */
    private $structureItem;

    /**
     * @var PageCacheable
     */
    private $pageCacheable;

    /**
     * @var SitemapCacheable
     */
    private $sitemapCacheable;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var RoutingItem|null
     */
    private $parent;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var ReplacementManager
     */
    private $replacementManager;

    /**
     * @var \ArrayIterator
     */
    private $iterator;

    /**
     * @var array
     */
    private $pageRoute = [];

    /**
     * Item constructor.
     * @param StructureItem $structureItem
     * @param CacheableInterface $pageCacheable
     * @param CacheableInterface $sitemapCacheable
     * @param CacheManager $cacheManager
     * @param SubManagerInterface $pageTypeSubManager
     * @param LocaleManager $localeManager
     * @param ReplacementManager $replacementManager
     * @param RoutingItem|null $parent
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function __construct(
        StructureItem $structureItem,
        CacheableInterface $pageCacheable,
        CacheableInterface $sitemapCacheable,
        CacheManager $cacheManager,
        SubManagerInterface $pageTypeSubManager,
        LocaleManager $localeManager,
        ReplacementManager $replacementManager,
        ?RoutingItem $parent = null
    ) {
        $this->pageCacheable = $pageCacheable;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->cacheManager = $cacheManager;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->parent = $parent;
        $this->localeManager = $localeManager;
        $this->replacementManager = $replacementManager;

        $structureItems = [];
        /** @var StructureItem $structItemChild */
        foreach ($structureItem->children() as $structItemChild) {
            $counter = 0;

            foreach ($this->localeManager->allActive() as $locale) {
                $locale = $locale['locale'];
                if (!\array_key_exists($locale, $structItemChild->pages())) {
                    continue;
                }

                /** @var Page $page */
                $page = $this->cacheManager->fetch(
                    $this->pageCacheable
                        ->withPageId($structItemChild->pages()[$locale])
                );

                if (empty($page)) {
                    continue;
                }

                $counter++;
                break;
            }

            if ($counter > 0) {
                $structureItems[] = clone $structItemChild;
            }
        }

        $this->structureItem = $structureItem->withChildrenInfo($structureItems);

        $this->iterator = new \ArrayIterator($this->structureItem->children());
    }

    public function structureItem(): StructureItem
    {
        return $this->structureItem;
    }

    /**
     * @return PageTypeInterface
     */
    public function pageType(): PageTypeInterface
    {
        return $this->pageTypeSubManager->get($this->structureItem()->pageType());
    }

    /**
     * @param string $locale
     * @throws \Psr\Cache\InvalidArgumentException
     * @return Page
     */
    public function page(string $locale): Page
    {
        if (!\array_key_exists($locale, $this->structureItem()->pages())) {
            throw new \Exception(\sprintf("Page with locale '%s' does not exists", $locale));
        }

        return $this->cacheManager->fetch(
            $this->pageCacheable
                ->withPageId($this->structureItem()->pages()[$locale])
        );
    }

    public function pageRoute(string $locale): ?RouteSpecification
    {
        if (\array_key_exists($locale, $this->pageRoute)) {
            return $this->pageRoute[$locale];
        }
        if (!\array_key_exists($locale, $this->structureItem()->pages())) {
            $this->pageRoute[$locale] = null;
            return null;
        }

        $page = $this->page($locale);

        $pageType = $this->pageType();
        $routing = '${PARENT}/${SLUG}';
        if ($pageType instanceof RoutingAwareInterface) {
            $routing = $pageType->routing();
        } elseif ($pageType instanceof RootPageTypeInterface) {
            $routing = '/';
        }

        $middleware = [
            LoadPageMiddleware::class,
            LoadSitemapMiddleware::class,
            LoadPageTypeMiddleware::class,
            LoadPageContentMiddleware::class,
        ];

        if ($pageType instanceof MiddlewarePageTypeInterface) {
            $middleware = \array_merge($middleware, \array_values($pageType->middleware()));
        }
        $middleware[] = RenderAction::class;

        $routeSpecification = (new RouteSpecification())
            ->withUri($routing)
            ->withPageId((string) $page->id())
            ->withMiddleware($middleware);

        foreach ($this->replacementManager->replacementServices() as $replacement) {
            $routeSpecification = $replacement->replace($routeSpecification, $locale, $this);
        }

        $this->pageRoute[$locale] = $routeSpecification;
        return $routeSpecification;
    }

    public function sitemap(): Sitemap
    {
        return $this->cacheManager->fetch(
            $this->sitemapCacheable
                ->withSitemapId($this->structureItem()->sitemapId())
        );
    }

    public function level(): int
    {
        return $this->structureItem()->level();
    }

    public function handle(): ?string
    {
        return $this->structureItem->handle();
    }

    public function parent(): ?RoutingItem
    {
        return $this->parent;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @return mixed
     */
    public function current()
    {
        return new RoutingItem(
            $this->iterator->current(),
            $this->pageCacheable,
            $this->sitemapCacheable,
            $this->cacheManager,
            $this->pageTypeSubManager,
            $this->localeManager,
            $this->replacementManager,
            $this
        );
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

    public function count()
    {
        return $this->iterator->count();
    }
}
