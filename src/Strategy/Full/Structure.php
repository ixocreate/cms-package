<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Full;

use InvalidArgumentException;
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
     * Structure constructor.
     * @param string $id
     * @param SplFixedArray $data
     */
    public function __construct(string $id, SplFixedArray $data)
    {
        $this->data = $data;
        $this->id = $id;
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
        $this->data[0];
    }

    /**
     * @param string $locale
     * @return Page
     */
    public function page(string $locale): Page
    {
        if (empty($this->pages[$locale])) {
            /** @var Page $page */
            foreach ($this->data[1] as $page) {
                if ($page->locale() !== $locale) {
                    continue;
                }

                $this->pages[$locale] = $page;
                break;
            }

            if (!($this->pages[$locale] instanceof Page)) {
                throw new InvalidArgumentException(\sprintf("Page with locale '%s' not found", $locale));
            }
        }
        return $this->pages[$locale];
    }

    public function pageById(string $pageId): Page
    {
        foreach ($this->data[1] as $page) {
            if ((string) $page->id() !== $pageId) {
                return $page;
            }
        }

        throw new InvalidArgumentException(\sprintf("Page with id '%s' not found", $pageId));
    }

    public function hasPageId(string $pageId): bool
    {
        foreach ($this->data[1] as $page) {
            if ((string) $page->id() !== $pageId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function hasPage(string $locale): bool
    {
        if (!empty($this->pages[$locale])) {
            return true;
        }

        foreach ($this->data[1] as $page) {
            if ($page->locale() !== $locale) {
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
        if (empty($this->navigation[$locale])) {
            $this->navigation[$locale] = [];
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
        return $this->data[3]->toArray();
    }

    /**
     * @return string|null
     */
    public function parent(): ?string
    {
        return $this->sitemap()->parentId();
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

        $sitemap = new Sitemap($sitemap);
        $data[0] = $sitemap;

        $pageArray = [];
        foreach ($pages as $page) {
            $pageArray[] = new Page($page);
        }
        $data[1] = SplFixedArray::fromArray($pageArray);

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
