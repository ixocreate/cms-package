<?php

declare(strict_types=1);

namespace Ixocreate\Cms\Site;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;

interface ItemInterface
{
    public function page(?string $locale): Page;
    public function sitemap(): Sitemap;
    public function children();
    public function hasChildren(): bool;
    public function handle(): ?string;
}
