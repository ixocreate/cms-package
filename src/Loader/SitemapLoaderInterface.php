<?php
namespace Ixocreate\Cms\Loader;

use Ixocreate\Cms\Entity\Sitemap;

interface SitemapLoaderInterface
{
    public function receiveSitemap(string $sitemapId): ?Sitemap;

    public function receiveHandles(): array;
}
