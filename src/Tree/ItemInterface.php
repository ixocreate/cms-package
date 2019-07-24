<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;

interface ItemInterface extends ContainerInterface
{
    /**
     * @return ContainerInterface
     */
    public function below(): ContainerInterface;

    /**
     * @return int
     */
    public function level(): int;

    /**
     * @return ItemInterface|null
     */
    public function parent(): ?ItemInterface;

    /**
     * @return PageTypeInterface
     */
    public function pageType(): PageTypeInterface;

    /**
     * @return string|null
     */
    public function handle(): ?string;

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
     * @return bool
     */
    public function isOnline(string $locale): bool;

    /**
     * @param string $locale
     * @return array
     */
    public function navigation(string $locale): array;
}
