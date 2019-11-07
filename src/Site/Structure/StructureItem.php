<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Structure;

final class StructureItem
{
    /**
     * @var string
     */
    private $sitemapId;

    /**
     * @var string
     */
    private $handle;

    /**
     * @var array
     */
    private $pages;

    /**
     * @var array
     */
    private $navigation;

    /**
     * @var array
     */
    private $childrenInfo;

    /**
     * @var array
     */
    private $children;

    /**
     * @var int
     */
    private $level;

    /**
     * @var StructureLoaderInterface
     */
    private $structureLoader;

    /**
     * @var string
     */
    private $pageType;

    /**
     * @var bool
     */
    private $forceLoading;

    public function __construct(
        string $id,
        StructureLoaderInterface $structureLoader
    ) {
        $item = $structureLoader->get($id);
        $this->sitemapId = $id;
        $this->handle = $item['handle'];
        $this->pages = $item['pages'];
        $this->navigation = $item['navigation'];
        $this->childrenInfo = $item['children'];
        $this->level = $item['level'];
        $this->pageType = $item['pageType'];
        $this->structureLoader = $structureLoader;
    }

    public function sitemapId(): string
    {
        return $this->sitemapId;
    }

    public function handle(): ?string
    {
        return $this->handle;
    }

    public function pages(): array
    {
        return $this->pages;
    }

    public function navigation(): array
    {
        return $this->navigation;
    }

    public function level(): int
    {
        return $this->level;
    }

    public function pageType(): string
    {
        return $this->pageType;
    }

    public function children(): array
    {
        if ($this->children === null) {
            $this->children = [];

            foreach ($this->childrenInfo as $item) {
                if ($item instanceof StructureItem) {
                    $this->children[] = $item;

                    continue;
                }

                $this->children[] = new StructureItem(
                    $item,
                    $this->structureLoader
                );
            }
        }

        return $this->children;
    }

    public function withChildrenInfo(array $childrenInfo): StructureItem
    {
        $item = clone $this;
        $item->childrenInfo = $childrenInfo;
        $item->children = null;

        return $item;
    }
}
