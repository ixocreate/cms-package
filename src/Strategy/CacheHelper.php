<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Strategy;

use Ixocreate\Cache\CacheManager;
use Ixocreate\Cms\Cacheable\CompiledGeneratorRoutesCacheable;
use Ixocreate\Cms\Cacheable\CompiledMatcherRoutesCacheable;
use Ixocreate\Cms\Entity\Page;

final class CacheHelper
{
    /**
     * @var Strategy
     */
    private $strategy;
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var CompiledGeneratorRoutesCacheable
     */
    private $compiledGeneratorRoutesCacheable;
    /**
     * @var CompiledMatcherRoutesCacheable
     */
    private $compiledMatcherRoutesCacheable;

    /**
     * @var null
     */
    private $sitemap = null;

    /**
     * @var null
     */
    private $page = null;

    /**
     * @var null
     */
    private $navigation = null;

    public function __construct(
        Strategy $strategy,
        CacheManager $cacheManager,
        CompiledGeneratorRoutesCacheable $compiledGeneratorRoutesCacheable,
        CompiledMatcherRoutesCacheable $compiledMatcherRoutesCacheable
    ) {

        $this->strategy = $strategy;
        $this->cacheManager = $cacheManager;
        $this->compiledGeneratorRoutesCacheable = $compiledGeneratorRoutesCacheable;
        $this->compiledMatcherRoutesCacheable = $compiledMatcherRoutesCacheable;
    }

    public function doSitemap(): CacheHelper
    {
        $helper = clone $this;
        $helper->sitemap = true;

        return $helper;
    }

    public function doPage(Page $page): CacheHelper
    {
        $helper = clone $this;
        $helper->page = $page;

        return $helper;
    }

    public function doNavigation(Page $page): CacheHelper
    {
        $helper = clone $this;
        $helper->navigation = $page;

        return $helper;
    }

    public function handle(): void
    {

        if (!empty($this->sitemap)) {
            $this->strategy->persistSitemap();
        }

        if (!empty($this->page)) {
            $this->strategy->persistPage($this->page);
        }

        if (!empty($this->navigation)) {
            $this->strategy->persistNavigation($this->navigation);
        }

        if (!empty($this->sitemap) || !empty($this->page)) {
            $this->cacheManager->fetch($this->compiledMatcherRoutesCacheable, true);
            $this->cacheManager->fetch($this->compiledGeneratorRoutesCacheable, true);
        }
    }
}
