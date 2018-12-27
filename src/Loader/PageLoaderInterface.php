<?php
namespace Ixocreate\Cms\Loader;

use Ixocreate\Cms\Entity\Page;

interface PageLoaderInterface
{
    public function receivePage(string $pageId): ?Page;
}
