<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Router;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Package\Action\Frontend\RenderAction;
use Ixocreate\Cms\Package\Cacheable\PageCacheable;
use Ixocreate\Cms\Package\Cacheable\PageVersionCacheable;
use Ixocreate\Cms\Package\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Package\Entity\Page;
use Ixocreate\Cms\Package\Entity\PageVersion;
use Ixocreate\Cms\Package\Entity\Sitemap;
use Ixocreate\Cms\Package\Middleware\LoadPageContentMiddleware;
use Ixocreate\Cms\Package\Middleware\LoadPageMiddleware;
use Ixocreate\Cms\Package\Middleware\LoadPageTypeMiddleware;
use Ixocreate\Cms\Package\Middleware\LoadSitemapMiddleware;
use Ixocreate\Cms\Package\PageType\MiddlewarePageTypeInterface;
use Ixocreate\Cms\Package\PageType\PageTypeInterface;
use Ixocreate\Cms\Package\PageType\PageTypeSubManager;
use Ixocreate\Cms\Package\PageType\RootPageTypeInterface;
use Ixocreate\Cms\Package\PageType\RoutingAwareInterface;
use Ixocreate\Cms\Package\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Package\Site\Structure\StructureItem;
use Ixocreate\Type\Package\Entity\SchemaType;
use Ixocreate\Cache\CacheableInterface;
use Ixocreate\ServiceManager\SubManager\SubManagerInterface;
use Ixocreate\Entity\Package\Type\Type;
use Ixocreate\Intl\Package\LocaleManager;

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
     * @var PageVersionCacheable
     */
    private $pageVersionCacheable;

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
     * @param CacheableInterface $pageVersionCacheable
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
        CacheableInterface $pageVersionCacheable,
        CacheManager $cacheManager,
        SubManagerInterface $pageTypeSubManager,
        LocaleManager $localeManager,
        ReplacementManager $replacementManager,
        ?RoutingItem $parent = null
    ) {
        $this->pageCacheable = $pageCacheable;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->pageVersionCacheable = $pageVersionCacheable;
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

                if ($page->status() === "offline") {
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
        return $this->pageTypeSubManager->get($this->sitemap()->pageType());
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
        if (array_key_exists($locale, $this->pageRoute)) {
            return $this->pageRoute[$locale];

        }
        if (!\array_key_exists($locale, $this->structureItem()->pages())) {
            $this->pageRoute[$locale] = null;
            return null;
        }

        $page = $this->page($locale);
        if ($page->status() === "offline") {
            $this->pageRoute[$locale] = null;
            return null;
        }

        $pageType = $this->pageType();
        $routing = '${PARENT}/${SLUG}';
        if ($pageType instanceof RoutingAwareInterface) {
            $routing = $pageType->routing();
        } else if ($pageType instanceof RootPageTypeInterface) {
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

    /**
     * @param string $locale
     * @throws \Psr\Cache\InvalidArgumentException
     * @return SchemaType
     */
    public function pageContent(string $locale): SchemaType
    {
        if (!\array_key_exists($locale, $this->structureItem()->pages())) {
            throw new \Exception(\sprintf("Page with locale '%s' does not exists", $locale));
        }

        $pageVersion = $this->cacheManager->fetch(
            $this->pageVersionCacheable
                ->withPageId($this->structureItem()->pages()[$locale])
        );

        if (!($pageVersion instanceof PageVersion)) {
            return Type::create([], SchemaType::serviceName());
        }

        return $pageVersion->content();
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
     * @return mixed
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function current()
    {
        return new RoutingItem(
            $this->iterator->current(),
            $this->pageCacheable,
            $this->sitemapCacheable,
            $this->pageVersionCacheable,
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
