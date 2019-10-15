<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cms\Entity\Navigation;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\NavigationRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;

final class StructureItemCacheable implements CacheableInterface
{
    /**
     * @var string;
     */
    private $id;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var NavigationRepository
     */
    private $navigationRepository;

    /**
     * SitemapCacheable constructor.
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository $pageRepository
     * @param NavigationRepository $navigationRepository
     */
    public function __construct(
        SitemapRepository $sitemapRepository,
        PageRepository $pageRepository,
        NavigationRepository $navigationRepository
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->pageRepository = $pageRepository;
        $this->navigationRepository = $navigationRepository;
    }

    public function withId(string $id): StructureItemCacheable
    {
        $cachable = clone $this;
        $cachable->id = $id;

        return $cachable;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {

        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($this->id);

        if (empty($sitemap)) {
            return [];
        }

        $children = [];
        $childrenResult = $this->sitemapRepository->findBy(['parentId' => $sitemap->id()], ['nestedLeft' => 'ASC']);
        /** @var Sitemap $item */
        foreach ($childrenResult as $item) {
            $children[] = (string) $item->id();
        }

        $pages = [];
        $pagesResult = $this->pageRepository->findBy(['sitemapId' => $sitemap->id()]);
        /** @var Page $item */
        foreach ($pagesResult as $item) {
            $pages[$item->locale()] = (string)$item->id();
        }

        $navigation = [];
        $navigationResult = $this->navigationRepository->findBy(['pageId' => \array_values($pages)]);
        /** @var Navigation $item */
        foreach ($navigationResult as $item) {
            $navigation[(string) $item->pageId()][] = $item->navigation();
        }

        return [
            'sitemapId' => $this->id,
            'handle' => $sitemap->handle(),
            'pageType' => $sitemap->pageType(),
            'pages' => $pages,
            'navigation' => $navigation,
            'children' => $children,
            'level' => $this->sitemapRepository->level($sitemap->id()),
        ];
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
        return 'structureitem.' . $this->id;
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 3600;
    }
}
