<?php

namespace KiwiSuite\Cms\Repository;


use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Metadata\PageVersionMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use KiwiSuite\Database\Repository\AbstractRepository;

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

