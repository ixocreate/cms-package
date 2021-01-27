<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Application\Package\PackageInterface;
use Ixocreate\Cms\Block\BlockBootstrapItem;
use Ixocreate\Cms\PageType\PageTypeBootstrapItem;

final class Package implements PackageInterface
{
    /**
     * @return array
     */
    public function getBootstrapItems(): array
    {
        return [
            PageTypeBootstrapItem::class,
            BlockBootstrapItem::class,
            CmsBootstrapItem::class,
        ];
    }

    /**
     * @return null|string
     */
    public function getBootstrapDirectory(): ?string
    {
        return __DIR__ . '/../bootstrap';
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [];
    }
}
