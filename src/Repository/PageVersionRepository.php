<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Repository;

use Ixocreate\Cms\Package\Entity\PageVersion;
use Ixocreate\Database\Package\Repository\AbstractRepository;

final class PageVersionRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return PageVersion::class;
    }
}
