<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Database;

use Ixocreate\Cache\CacheInterface;
use Ixocreate\Cms\Entity\Navigation;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\NavigationRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Strategy\LoaderInterface;
use Ixocreate\Cms\Strategy\StructureInterface;
use SplFixedArray;

final class Loader implements LoaderInterface
{
    /**
     * @var bool
     */
    private $initialized = false;
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
     * @var array
     */
    private $list = [];

    /**
     * @var array
     */
    private $root = [];


    public function __construct(SitemapRepository $sitemapRepository, PageRepository $pageRepository, NavigationRepository $navigationRepository)
    {
        $this->sitemapRepository = $sitemapRepository;
        $this->pageRepository = $pageRepository;
        $this->navigationRepository = $navigationRepository;
    }

    private function initialize(): void
    {
        if ($this->initialized === true) {
            return;
        }

        $this->initialized = true;

        $result = $this->sitemapRepository->findBy([], ['nestedLeft' => 'ASC']);

        /** @var Sitemap $sitemap */
        foreach ($result as $key => $sitemap) {
            $this->list[(string) $sitemap->id()] = [
                'sitemap' => $sitemap,
                'pages' => [],
                'children' => [],
                'navigation' => [],
                'level' => 0,
            ];

            if (empty($sitemap->parentId())) {
                $this->root[] = (string) $sitemap->id();
            } else if (isset($this->list[(string) $sitemap->parentId()])) {
                $this->list[(string) $sitemap->parentId()]['children'][] = (string) $sitemap->id();
            }

            unset($result[$key]);
        }

        foreach ($this->root as $root) {
            $this->recursiveBuildLevel($this->list, $this->list[$root]['children'], 0);
        }

        $navigation = [];
        $result = $this->navigationRepository->findAll();
        /** @var Navigation $item */
        foreach ($result as $key => $item) {
            $pageId = (string) $item->pageId();
            if (!\array_key_exists($pageId, $navigation)) {
                $navigation[$pageId] = [];
            }

            $navigation[$pageId][] = $item->navigation();

            unset($result[$key]);
        }

        $result = $this->pageRepository->findAll();
        /** @var Page $page */
        foreach ($result as $key => $page) {
            if (empty($this->list[(string) $page->sitemapId()])) {
                continue;
            }

            $this->list[(string) $page->sitemapId()]['pages'][$page->locale()] = $page;
            $this->list[(string) $page->sitemapId()]['navigation'][$page->locale()] = [];
            if (\array_key_exists((string) $page->id(), $navigation)) {
                $this->list[(string) $page->sitemapId()]['navigation'][$page->locale()] = $navigation[(string) $page->id()];
            }

            unset($result[$key]);
        }
    }

    private function recursiveBuildLevel(array &$tree, array $items, int $level): void
    {
        foreach ($items as $id) {
            $tree[$id]['level'] = $level;
            $this->recursiveBuildLevel($tree, $tree[$id]['children'], $level + 1);
        }
    }

    /**
     * @return string[]
     */
    public function root(): array
    {
        $this->initialize();

        return $this->root;
    }

    public function get(string $id): StructureInterface
    {
        $this->initialize();

        return new Structure(
            $this->list[$id]['sitemap'],
            $this->list[$id]['pages'],
            $this->list[$id]['navigation'],
            $this->list[$id]['children'],
            $this->list[$id]['level']
        );
    }
}
