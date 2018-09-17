<?php
namespace KiwiSuite\Cms\Site\Structure;

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
        array $children,
        int $level
    ) {
        $this->sitemapId = $sitemapId;
        $this->handle = $handle;
        $this->pages = $pages;
        $this->navigation = $navigation;
        $this->children = $children;
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
        $children = [];

        foreach ($this->children as $item) {
            $children[] = new StructureItem(
                $item['sitemapId'],
                $item['handle'],
                $item['pages'],
                $item['navigation'],
                $item['children'],
                $this->level() + 1
            );
        }

        return $children;
    }
}