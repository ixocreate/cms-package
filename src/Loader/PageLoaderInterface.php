<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Loader;

use Ixocreate\Cms\Entity\Page;

interface PageLoaderInterface
{
    public function receivePage(string $pageId): ?Page;
}
