<?php

namespace KiwiSuite\Cms\Repository;


use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Metadata\PageMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use KiwiSuite\Database\Repository\AbstractRepository;

final class PageRepository extends AbstractRepository
{
    
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Page::class;
    }
    
    public function loadMetadata(ClassMetadataBuilder $builder): void
    {
        $metadata = (new PageMetadata($builder));
    }
    
}

