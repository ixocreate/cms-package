<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;

interface StructureInterface
{
    /**
     * @return string
     */
    public function id(): string;

    /**
     * @return Sitemap
     */
    public function sitemap(): Sitemap;

    /**
     * @param string $locale
     * @return bool
     */
    public function hasPage(string $locale): bool;

    /**
     * @param string $pageId
     * @return bool
     */
    public function hasPageId(string $pageId): bool;

    /**
     * @param string $locale
     * @return Page
     */
    public function page(string $locale): Page;

    /**
     * @param string $pageId
     * @return Page
     */
    public function pageById(string $pageId): Page;

    /**
     * @param string $locale
     * @return string[]
     */
    public function navigation(string $locale): array;

    /**
     * @return string|null
     */
    public function handle(): ?string;

    /**
     * @return string
     */
    public function pageType(): string;

    /**
     * @return string|null
     */
    public function parent(): ?string;

    /**
     * @return string[]
     */
    public function children(): array;

    /**
     * @return int
     */
    public function level(): int;
}
