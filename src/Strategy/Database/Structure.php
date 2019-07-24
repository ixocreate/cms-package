<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Database;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Strategy\StructureInterface;

final class Structure implements StructureInterface
{
    /**
     * @var Page[]
     */
    private $pages = [];

    /**
     * @var array
     */
    private $navigation = [];

    /**
     * @var string[]
     */
    private $children = [];

    /**
     * @var int
     */
    private $level;

    /**
     * @var Sitemap
     */
    private $sitemap;

    /**
     * Structure constructor.
     * @param Sitemap $sitemap
     * @param array $pages
     * @param array $navigations
     * @param array $children
     * @param int $level
     */
    public function __construct(Sitemap $sitemap, array $pages, array $navigations, array $children, int $level)
    {
        $this->sitemap = $sitemap;
        $this->pages = $pages;
        $this->navigation = $navigations;
        $this->children = $children;
        $this->level = $level;
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
        return $this->sitemap;
    }

    /**
     * @param string $locale
     * @return Page
     */
    public function page(string $locale): Page
    {
        return $this->pages[$locale];
    }

    public function pageById(string $pageId): Page
    {
        foreach ($this->pages as $page) {
            if ((string) $page->id() === $pageId) {
                return $page;
            }
        }
    }

    public function hasPage(string $locale): bool
    {
        return \array_key_exists($locale, $this->pages);
    }

    /**
     * @param string $pageId
     * @return bool
     */
    public function hasPageId(string $pageId): bool
    {
        foreach ($this->pages as $page) {
            if ((string) $page->id() === $pageId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $locale
     * @return array
     */
    public function navigation(string $locale): array
    {
        return $this->navigation[$locale];
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
     * @return string[]
     */
    public function children(): array
    {
        return $this->children;
    }

    public function parent(): ?string
    {
        if (empty($this->sitemap()->parentId())) {
            return null;
        }

        return (string) $this->sitemap()->parentId();
    }

    public function level(): int
    {
        return $this->level;
    }
}
