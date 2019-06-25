<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Cacheable;

use Ixocreate\Cache\CacheableInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Router\Replacement\ReplacementManager;
use Ixocreate\Cms\Router\RouteCollection;
use Ixocreate\Cms\Router\Tree\Factory;
use Ixocreate\Cms\Tree\Structure\StructureBuilder;
use Ixocreate\Intl\LocaleManager;
use Symfony\Component\Routing\Generator\Dumper\CompiledUrlGeneratorDumper;

final class CompiledGeneratorRoutesCacheable implements CacheableInterface
{
    /**
     * @var LocaleManager
     */
    private $localeManager;
    /**
     * @var StructureBuilder
     */
    private $structureBuilder;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var ReplacementManager
     */
    private $replacementManager;

    public function __construct(
        LocaleManager $localeManager,
        StructureBuilder $structureBuilder,
        PageTypeSubManager $pageTypeSubManager,
        ReplacementManager $replacementManager
    ) {
        $this->localeManager = $localeManager;
        $this->structureBuilder = $structureBuilder;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->replacementManager = $replacementManager;
    }

    /**
     * @return mixed
     */
    public function uncachedResult()
    {
        return (new CompiledUrlGeneratorDumper(
            (new RouteCollection($this->localeManager))
                ->build(
                    (new Factory($this->pageTypeSubManager, $this->replacementManager))
                        ->createContainer($this->structureBuilder->build())
                )
        ))->getCompiledRoutes();
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
        return 'compiled.url.generator';
    }

    /**
     * @return int
     */
    public function cacheTtl(): int
    {
        return 0;
    }
}
