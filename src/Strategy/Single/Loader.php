<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy\Single;

use Ixocreate\Cache\CacheInterface;
use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Strategy\LoaderInterface;
use Ixocreate\Cms\Strategy\StructureInterface;
use RuntimeException;
use SplFixedArray;

final class Loader implements LoaderInterface
{
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var StructureCacheable
     */
    private $structureCacheable;
    /**
     * @var SitemapCacheable
     */
    private $sitemapCacheable;
    /**
     * @var PageCacheable
     */
    private $pageCacheable;
    /**
     * @var RootCacheable
     */
    private $rootCacheable;

    public function __construct(
        CacheManager $cacheManager,
        StructureCacheable $structureCacheable,
        RootCacheable $rootCacheable,
        SitemapCacheable $sitemapCacheable,
        PageCacheable $pageCacheable
    ) {

        $this->cacheManager = $cacheManager;
        $this->structureCacheable = $structureCacheable;
        $this->rootCacheable = $rootCacheable;
        $this->sitemapCacheable = $sitemapCacheable;
        $this->pageCacheable = $pageCacheable;
    }

    /**
     * @return string[]
     */
    public function root(): array
    {
        return $this->cacheManager->fetch(
            $this->rootCacheable
        );
    }

    public function get(string $id): StructureInterface
    {
        $splFixedArray = $this->cacheManager->fetch(
            $this->structureCacheable->withSitemapId($id)
        );

        return new Structure(
            $this->cacheManager,
            $this->sitemapCacheable,
            $this->pageCacheable,
            $splFixedArray
        );
    }
}
