<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\Tree\Structure\StructureItem;

interface ItemInterface extends ContainerInterface
{
    public function sitemap(): Sitemap;

    public function hasPage(string $locale = null): bool;

    public function page(string $locale): Page;

    public function isOnline(string $locale): bool;

    public function pageType(): PageTypeInterface;

    public function handle(): ?string;

    public function navigation(string $locale): array;

    public function level(): int;

    public function parent(): ?ItemInterface;

    public function structureItem(): StructureItem;

    public function below(): ContainerInterface;
}
