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

    public function __construct(
        string $sitemapId,
        ?string $handle,
        array $pages,
        array $navigation,
        array $childrenInfo,
        int $level
    ) {
        $this->sitemapId = $sitemapId;
        $this->handle = $handle;
        $this->pages = $pages;
        $this->navigation = $navigation;
        $this->childrenInfo = $childrenInfo;
        $this->level = $level;
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
                    $item['sitemapId'],
                    $item['handle'],
                    $item['pages'],
                    $item['navigation'],
                    $item['children'],
                    $this->level() + 1
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
