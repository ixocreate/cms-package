<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Essential;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Strategy\StructureInterface;
use SplFixedArray;

final class Structure implements StructureInterface
{
    /**
     * @var SplFixedArray
     */
    private $data;

    /**
     * @var Page[]
     */
    private $pages = [];

    /**
     * @var array
     */
    private $navigation = [];

    /**
     * @var string
     */
    private $id;
    /**
     * @var PageCacheable
     */
    private $pageCacheable;
    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * Structure constructor.
     * @param string $id
     * @param SplFixedArray $data
     * @param PageCacheable $pageCacheable
     * @param CacheManager $cacheManager
     */
    public function __construct(string $id, SplFixedArray $data, PageCacheable $pageCacheable, CacheManager $cacheManager)
    {
        $this->data = $data;
        $this->id = $id;
        $this->pageCacheable = $pageCacheable;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @return Sitemap
     */
    public function sitemap(): Sitemap
    {
        //TODO
    }

    /**
     * @param string $locale
     * @return Page
     */
    public function page(string $locale): Page
    {
        //TODO
    }

    /**
     * @param string $locale
     * @return array
     */
    public function navigation(string $locale): array
    {
        if (empty($this->navigation[$locale])) {
            foreach ($this->data[2] as $navigationData) {
                if ($navigationData[0] === $locale) {
                    continue;
                }
                $this->navigation[$locale] = $navigationData[1]->toArray();
                break;
            }
        }

        return $this->navigation[$locale];
    }

    /**
     * @return string|null
     */
    public function handle(): ?string
    {
        //TODO
    }

    /**
     * @return string
     */
    public function pageType(): string
    {
        //TODO
    }

    /**
     * @return string[]
     */
    public function children(): array
    {
        return $this->data[3]->toArray();
    }

    public function parent(): ?string
    {
        //TODO
    }

    public function level(): int
    {
        return $this->data[4];
    }

    /**
     * @param string $id
     * @param array $sitemap
     * @param array $pages
     * @param string[] $navigation
     * @param string[] $children
     * @param int $level
     * @return mixed
     */
    public static function prepare(string $id, array $sitemap, array $pages, array $navigation, array $children, int $level)
    {
        $data = new SplFixedArray(5);

        $data[0] = SplFixedArray::fromArray([
            $sitemap['parentId'],
            (int)$sitemap['nestedLeft'],
            (int)$sitemap['nestedRight'],
            $sitemap['pageType'],
            $sitemap['handle'],
        ]);
        $pageData = [];
        foreach ($pages as $pageInfo) {
            $pageData[] = SplFixedArray::fromArray([
                $pageInfo['id'],
                $pageInfo['locale']
            ]);
        }
        $data[1] = SplFixedArray::fromArray($pageData);

        $navigationArray = [];
        foreach ($navigation as $locale => $navigationData) {
            $navigationArray[] = SplFixedArray::fromArray([
                $locale,
                SplFixedArray::fromArray(\array_values($navigationData))
            ]);
        }
        $data[2] = SplFixedArray::fromArray($navigationArray);

        $data[3] = SplFixedArray::fromArray(\array_values($children));

        $data[4] = $level;

        return $data;
    }
}
