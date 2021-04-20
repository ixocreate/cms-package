<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Navigation;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Site\ItemInterface;

final class Item implements ItemInterface
{
    /**
     * @var Page
     */
    private $page;

    /**
     * @var Sitemap
     */
    private $sitemap;

    /**
     * @var int
     */
    private $level;

    /**
     * @var Item[]
     */
    private $children;

    /**
     * Item constructor.
     * @param Page $page
     * @param Sitemap $sitemap
     * @param int $level
     * @param array $children
     * @param bool $active
     */
    public function __construct(Page $page, Sitemap $sitemap, int $level, array $children = [])
    {
        $this->page = $page;
        $this->sitemap = $sitemap;
        $this->level = $level;
        $this->children = $children;
    }

    /**
     * @return int
     */
    public function level(): int
    {
        return $this->level;
    }

    /**
     * @return Page
     */
    public function page(?string $page): Page
    {
        return $this->page;
    }

    /**
     * @return Sitemap
     */
    public function sitemap(): Sitemap
    {
        return $this->sitemap;
    }

    public function handle(): ?string
    {
        return $this->sitemap->handle();
    }

    public function isActive(?Sitemap $sitemap): bool
    {
        if ($sitemap === null) {
            return false;
        }

        if ((string)$sitemap->id() === (string)$this->sitemap()->id()) {
            return true;
        }

        if ($sitemap->nestedLeft() > $this->sitemap()->nestedLeft() && $sitemap->nestedRight() < $this->sitemap()->nestedRight()) {
            return true;
        }

        return false;
    }

    /**
     * @return Item[]
     */
    final public function children(): array
    {
        return $this->children;
    }

    final public function hasChildren(): bool
    {
        return \count($this->children) > 0;
    }
}
