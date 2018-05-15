<?php

namespace KiwiSuite\Cms\Repository;


use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Metadata\SitemapMetadata;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use KiwiSuite\Database\Tree\TreeRepository;

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

