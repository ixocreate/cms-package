<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Loader;

use Ixocreate\Cms\Package\Entity\Sitemap;

interface SitemapLoaderInterface
{
    public function receiveSitemap(string $sitemapId): ?Sitemap;

    public function receiveHandles(): array;
}
