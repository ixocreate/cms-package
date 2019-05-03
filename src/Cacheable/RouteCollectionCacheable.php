<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Router\RouteSpecification;
use Ixocreate\Cms\Router\RoutingItem;
use Ixocreate\Cms\Site\Structure\StructureItem;
use Ixocreate\Intl\LocaleManager;
use RecursiveIteratorIterator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

final class RouteCollectionCacheable implements CacheableInterface
{
    /**
     * @var StructureCacheable
     */
    private $structureCacheable;

    /**
     * @var LocaleManager
     */
    private $localeManager;

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
     * @var ReplacementManager
     */
    private $replacementManager;

    /**
     * SitemapCacheable constructor.
     * @param StructureCacheable $structureCacheable
     * @param LocaleManager $localeManager
     * @param PageCacheable $pageCacheable
     * @param SitemapCacheable $sitemapCacheable
     * @param CacheManager $cacheManager
     * @param PageTypeSubManager $pageTypeSubManager
     * @param ReplacementManager $replacementManager
     */
    public function __construct(
        StructureCacheable $structureCacheable,
        LocaleManager $localeManager,
        PageCacheable $pageCacheable,
        SitemapCacheable $sitemapCacheable,
        CacheManager $cacheManager,
        PageTypeSubManager $pageTypeSubManager,
        ReplacementManager $replacementManager
    ) {
        $this->structureCacheable = $structureCacheable;
        $this->localeManager = $localeManager;
        $this->pageCacheable = $pageCacheable;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->cacheManager = $cacheManager;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->replacementManager = $replacementManager;
    }

    /**
     * @throws \Psr\Cache\InvalidArgumentException
     * @return mixed
     */
    public function uncachedResult()
    {
        $structure = $this->cacheManager->fetch($this->structureCacheable);

        $rootStructure = new StructureItem(
            '',
            '',
            [],
            [],
            $structure,
            0
        );

        $routerItem = new RoutingItem(
            $rootStructure,
            $this->pageCacheable,
            $this->sitemapCacheable,
            $this->cacheManager,
            $this->pageTypeSubManager,
            $this->localeManager,
            $this->replacementManager
        );

        $routeCollection = new RouteCollection();

        $iterator = new \RecursiveIteratorIterator($routerItem, RecursiveIteratorIterator::SELF_FIRST);
        /** @var RoutingItem $routingItem */
        foreach ($iterator as $routingItem) {
            foreach ($this->localeManager->allActive() as $locale) {
                $locale = $locale['locale'];
                $routeSpecification = $routingItem->pageRoute($locale);
                if (empty($routeSpecification)) {
                    continue;
                }
                foreach ($routeSpecification->uris() as $name => $uri) {
                    if ($name === RouteSpecification::NAME_INHERITANCE) {
                        continue;
                    }

                    $routePrefix = 'page.';
                    if ($name !== RouteSpecification::NAME_MAIN) {
                        $routePrefix .= $name . '.';
                    }

                    $uriParts = \parse_url($uri);

                    $routeObj = new Route(($uriParts['path']) ?? '/');
                    if (!empty($uriParts['host'])) {
                        $routeObj->setHost($uriParts['host']);
                        $routeObj->setSchemes($uriParts['scheme']);
                    }

                    $routeObj->setDefault('pageId', $routeSpecification->pageId());
                    $routeObj->setDefault('locale', $locale);
                    $routeObj->setDefault('middleware', $routeSpecification->middleware());

                    $routeName = $routePrefix . $routeSpecification->pageId();
                    $routeCollection->add($routeName, $routeObj);
                }
            }
        }
        return $routeCollection;
    }

    /**
     * @return string
     */
    public function cacheName(): string
    {
        return 'cms';
    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return 'routing';
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 3600;
    }
}
