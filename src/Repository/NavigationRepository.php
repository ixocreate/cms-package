<?php

namespace Ixocreate\Cms\Repository;


use Ixocreate\Cms\Entity\Navigation;
use Ixocreate\Cms\Metadata\NavigationMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Ixocreate\Database\Repository\AbstractRepository;

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

