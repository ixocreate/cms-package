<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Loader;

use Ixocreate\Package\Cms\Entity\Page;

interface PageLoaderInterface
{
    public function receivePage(string $pageId): ?Page;
}
