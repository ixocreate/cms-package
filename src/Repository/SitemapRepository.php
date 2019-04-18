<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Repository;

use Ixocreate\Package\Cms\Entity\Sitemap;
use Ixocreate\Package\Database\Tree\TreeRepository;

final class SitemapRepository extends TreeRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Sitemap::class;
    }
}
