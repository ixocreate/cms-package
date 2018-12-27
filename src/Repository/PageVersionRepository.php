<?php

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

