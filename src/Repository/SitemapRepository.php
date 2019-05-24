<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Repository;

use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Database\Tree\TreeRepository;

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
