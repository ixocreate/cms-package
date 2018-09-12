<?php
namespace KiwiSuite\Cms\Loader;

use KiwiSuite\Cms\Entity\Page;

interface PageLoaderInterface
{
    public function receivePage(string $pageId): ?Page;
}