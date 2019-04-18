<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Loader;

use Ixocreate\Cms\Package\Entity\Page;

interface PageLoaderInterface
{
    public function receivePage(string $pageId): ?Page;
}
