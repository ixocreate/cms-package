<?php
namespace KiwiSuite\Cms\Loader;

use KiwiSuite\Cms\Entity\Sitemap;

interface SitemapLoaderInterface
{
    public function receiveSitemap(string $sitemapId): ?Sitemap;

    public function receiveHandles(): array;
}