<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cms\Site\Structure\StructureBuilder;

final class StructureCacheable implements CacheableInterface
{
    /**
     * @var StructureBuilder
     */
    private $structureBuilder;

    /**
     * SitemapCacheable constructor.
     * @param StructureBuilder $structureBuilder
     */
    public function __construct(StructureBuilder $structureBuilder)
    {
        $this->structureBuilder = $structureBuilder;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        return $this->structureBuilder->build()->structure();
    }

    /**
     * @return string
     */
    public function cacheName(): string
    {
        return 'cms';
    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return 'structure';
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 3600;
    }
}
