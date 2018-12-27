<?php

namespace Ixocreate\Cms\Repository;


use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Metadata\SitemapMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
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
    
    public function loadMetadata(ClassMetadataBuilder $builder): void
    {
        $metadata = (new SitemapMetadata($builder));
    }
    
}

