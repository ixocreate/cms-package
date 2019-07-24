<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Single;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Strategy\StructureInterface;
use SplFixedArray;

final class Structure implements StructureInterface
{
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var SitemapCacheable
     */
    private $sitemapCacheable;
    /**
     * @var PageCacheable
     */
    private $pageCacheable;
    /**
     * @var SplFixedArray
     */
    private $data;

    public function __construct(
        CacheManager $cacheManager,
        SitemapCacheable $sitemapCacheable,
        PageCacheable $pageCacheable,
        SplFixedArray $data
    ) {
        $this->cacheManager = $cacheManager;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->pageCacheable = $pageCacheable;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return (string) $this->sitemap()->id();
    }

    /**
     * @return Sitemap
     */
    public function sitemap(): Sitemap
    {
        return $this->cacheManager->fetch(
            $this->sitemapCacheable->withSitemapId($this->data[0])
        );
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function hasPage(string $locale): bool
    {
        foreach ($this->data[1] as $pageData) {
            if ($pageData[0] === $locale) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $pageId
     * @return bool
     */
    public function hasPageId(string $pageId): bool
    {
        foreach ($this->data[1] as $pageData) {
            if ($pageData[1] === $pageId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $locale
     * @return Page
     */
    public function page(string $locale): Page
    {
        foreach ($this->data[1] as $pageData) {
            if ($pageData[0] === $locale) {
                return $this->cacheManager->fetch(
                    $this->pageCacheable->withPageId($pageData[1])
                );
            }
        }
    }

    /**
     * @param string $pageId
     * @return Page
     */
    public function pageById(string $pageId): Page
    {
        foreach ($this->data[1] as $pageData) {
            if ($pageData[1] === $pageId) {
                return $this->cacheManager->fetch(
                    $this->pageCacheable->withPageId($pageData[1])
                );
            }
        }
    }

    /**
     * @param string $locale
     * @return string[]
     */
    public function navigation(string $locale): array
    {
        foreach ($this->data[2] as $navigationData) {
            if ($navigationData[0] === $locale) {
                return $navigationData[1]->toArray();
            }
        }

        return [];
    }

    /**
     * @return string|null
     */
    public function handle(): ?string
    {
        return $this->sitemap()->handle();
    }

    /**
     * @return string
     */
    public function pageType(): string
    {
        return $this->sitemap()->pageType();
    }

    /**
     * @return string|null
     */
    public function parent(): ?string
    {
        return $this->sitemap()->parentId();
    }

    /**
     * @return string[]
     */
    public function children(): array
    {
        return $this->data[3]->toArray();
    }

    /**
     * @return int
     */
    public function level(): int
    {
        return $this->data[4];
    }

    public static function prepare(string $sitemap, array $pages, array $navigation, array $children, int $level): SplFixedArray
    {
        $preparedPages = [];
        foreach ($pages as $locale => $page) {
            $preparedPages[] = SplFixedArray::fromArray([
                $locale,
                $page
            ]);
        }

        $preparedNavigation = [];
        foreach ($navigation as $locale => $nav) {
            $preparedNavigation[] = SplFixedArray::fromArray([
                $locale,
                SplFixedArray::fromArray(\array_values($nav))
            ]);
        }

        return SplFixedArray::fromArray([
            $sitemap,
            SplFixedArray::fromArray($preparedPages),
            SplFixedArray::fromArray($preparedNavigation),
            SplFixedArray::fromArray(\array_values($children)),
            $level
        ]);
    }
}
