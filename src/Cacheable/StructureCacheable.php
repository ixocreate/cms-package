<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cms\Tree\Structure\Structure;
use Ixocreate\Cms\Tree\Structure\StructureBuilder;

final class StructureCacheable implements CacheableInterface
{
    /**
     * @var StructureBuilder
     */
    private $structureBuilder;

    public function __construct(
        StructureBuilder $structureBuilder
    ) {
        $this->structureBuilder = $structureBuilder;
    }

    /**
     * @throws \Exception
     * @return Structure
     */
    public function uncachedResult()
    {
        return $this->structureBuilder->build();
    }

    /**
     * @return string
     */
    public function cacheName(): string
    {
        return 'cms_store';
    }

    /**
     * @return string
     */
    public function cacheKey(): string
    {
        return 'compiled.url.matcher';
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 0;
    }
}
