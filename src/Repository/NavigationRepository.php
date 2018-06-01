<?php

namespace KiwiSuite\Cms\Repository;


use KiwiSuite\Cms\Entity\Navigation;
use KiwiSuite\Cms\Metadata\NavigationMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use KiwiSuite\Database\Repository\AbstractRepository;

final class NavigationRepository extends AbstractRepository
{
    
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Navigation::class;
    }
    
    public function loadMetadata(ClassMetadataBuilder $builder): void
    {
        $metadata = (new NavigationMetadata($builder));
    }
}

