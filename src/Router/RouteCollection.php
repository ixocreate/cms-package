<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router;

use Doctrine\ORM\EntityManagerInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Entity\RouteMatch;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\RouteMatchRepository;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Site\Structure\StructureBuilder;
use Ixocreate\Cms\Site\Structure\StructureItem;
use Ixocreate\Intl\LocaleManager;
use RecursiveIteratorIterator;
use Symfony\Component\Routing\Route;

final class RouteCollection
{
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
     * @var StructureBuilder
     */
    private $structureBuilder;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RouteMatchRepository
     */
    private $routeMatchRepository;

    public function __construct(
        LocaleManager $localeManager,
        PageCacheable $pageCacheable,
        SitemapCacheable $sitemapCacheable,
        CacheManager $cacheManager,
        PageTypeSubManager $pageTypeSubManager,
        ReplacementManager $replacementManager,
        StructureBuilder $structureBuilder,
        EntityManagerInterface $master,
        RouteMatchRepository $routeMatchRepository
    ) {
        $this->localeManager = $localeManager;
        $this->pageCacheable = $pageCacheable;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->cacheManager = $cacheManager;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->replacementManager = $replacementManager;
        $this->structureBuilder = $structureBuilder;
        $this->entityManager = $master;
        $this->routeMatchRepository = $routeMatchRepository;
    }

    public function build(): \Symfony\Component\Routing\RouteCollection
    {
        $structure = $this->structureBuilder->build();

        $structureItem = new StructureItem(
            'root',
            $structure->structureLoader()
        );

        $routeCollection = new \Symfony\Component\Routing\RouteCollection();

        $routerItem = new RoutingItem(
            $structureItem,
            $this->pageCacheable,
            $this->sitemapCacheable,
            $this->cacheManager,
            $this->pageTypeSubManager,
            $this->localeManager,
            $this->replacementManager
        );

        $routeMatches = [];

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

                    if (\mb_strpos($uri, '{') === false) {
                        $routeMatches[] = new RouteMatch([
                            'url' => (string) $uri,
                            'type' => $name,
                            'pageId' => (string) $routeSpecification->pageId(),
                            'middleware' => (empty($routeSpecification->middleware())) ? [] : $routeSpecification->middleware(),
                        ]);
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

        $this->entityManager->transactional(function () use ($routeMatches) {
            $dql = 'DELETE FROM ' . RouteMatch::class . ' r WHERE r.url IS NOT NULL';
            $this->routeMatchRepository->createQuery($dql)->execute();

            foreach ($routeMatches as $routeMatch) {
                $this->routeMatchRepository->save($routeMatch);
            }
        });

        return $routeCollection;
    }
}
