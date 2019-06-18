<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree\Structure;

use Countable;
use Iterator;

final class StructureItem implements Iterator, Countable
{
    /**
     * @var int
     */
    private $level;

    /**
     * @var array
     */
    private $itemData;

    /**
     * @var Structure
     */
    private $structure;

    /**
     * @var StructureItem|null
     */
    private $parent;

    /**
     * @var StructureStore
     */
    private $structureStore;

    /**
     * @var string
     */
    private $structureKey;

    public function __construct(StructureStore $structureStore, string $structureKey, int $level, ?StructureItem $parent = null)
    {
        $this->structureStore = $structureStore;
        $this->structureKey = $structureKey;
        $this->level = $level;
        $this->parent = $parent;
    }

    /**
     * @throws \Exception
     * @return array
     */
    private function itemData(): array
    {
        if ($this->itemData === null) {
            $this->itemData = $this->structureStore->item($this->structureKey());
        }

        return $this->itemData;
    }

    /**
     * @throws \Exception
     * @return string
     */
    public function structureKey(): string
    {
        return $this->structureKey;
    }

    /**
     * @return string
     */
    public function sitemapId(): string
    {
        return $this->sitemapData()['id'];
    }

    /**
     * @throws \Exception
     * @return array
     */
    public function sitemapData(): array
    {
        return $this->itemData()['sitemap'];
    }

    /**
     * @param string $locale
     * @return string
     */
    public function pageId(string $locale): string
    {
        $pageData = $this->pageData($locale);
        if (empty($pageData)) {
            return '';
        }

        return $this->pageData($locale)['id'];
    }

    /**
     * @param string $locale
     * @throws \Exception
     * @return array
     */
    public function pageData(string $locale): array
    {
        if (!\array_key_exists($locale, $this->itemData()['pages'])) {
            return [];
        }

        return $this->itemData()['pages'][$locale];
    }

    /**
     * @throws \Exception
     * @return string
     */
    public function pageType(): string
    {
        return $this->sitemapData()['pageType'];
    }

    /**
     * @throws \Exception
     * @return string|null
     */
    public function handle(): ?string
    {
        return $this->sitemapData()['handle'];
    }

    /**
     * @param string $locale
     * @throws \Exception
     * @return array
     */
    public function navigation(string $locale): array
    {
        if (!\array_key_exists($locale, $this->itemData()['navigation'])) {
            return [];
        }
        return $this->itemData()['navigation'][$locale];
    }

    /**
     * @return int
     */
    public function level(): int
    {
        return $this->level;
    }

    /**
     * @param callable $filter
     * @throws \Exception
     * @return StructureItem
     */
    public function only(callable $filter): StructureItem
    {
        $item = clone $this;
        $item->itemData();
        $item->structure = $this->structure()->only($filter);
        $item->itemData['children'] = [];
        foreach ($item->structure as $structureItem) {
            $item->itemData['children'][] = $structureItem->structureKey();
        }

        return $item;
    }

    /**
     * @throws \Exception
     * @return Structure
     */
    public function structure(): Structure
    {
        if ($this->structure === null) {
            $this->structure = new Structure(
                $this->structureStore,
                $this->itemData()['children'],
                $this->level() + 1,
                $this
            );
        }
        return $this->structure;
    }

    /**
     * @return StructureItem|null
     */
    public function parent(): ?StructureItem
    {
        return $this->parent;
    }

    /**
     * @throws \Exception
     * @return StructureItem
     */
    public function current()
    {
        return $this->structure()->current();
    }

    /**
     *
     * @throws \Exception
     */
    public function next()
    {
        $this->structure()->next();
    }

    /**
     * @throws \Exception
     * @return mixed|void
     */
    public function key()
    {
        return $this->structure()->key();
    }

    /**
     * @throws \Exception
     * @return bool|void
     */
    public function valid()
    {
        return $this->structure()->valid();
    }

    /**
     *
     * @throws \Exception
     */
    public function rewind()
    {
        $this->structure()->rewind();
    }

    /**
     * @throws \Exception
     * @return int
     */
    public function count()
    {
        return $this->structure()->count();
    }
}
