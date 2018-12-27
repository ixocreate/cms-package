<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Repository;

use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Metadata\PageVersionMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Database\Repository\AbstractRepository;

final class PageVersionRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return PageVersion::class;
    }

    public function loadMetadata(ClassMetadataBuilder $builder): void
    {
        $metadata = (new PageVersionMetadata($builder));
    }
}
