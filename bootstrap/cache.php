<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Cache;

use Ixocreate\Cache\CacheConfigurator;
use Ixocreate\Cache\Option\Chain;
use Ixocreate\Cache\Option\Filesystem;
use Ixocreate\Cache\Option\InMemory;
use Ixocreate\Cms\Cacheable\CompiledGeneratorRoutesCacheable;
use Ixocreate\Cms\Cacheable\CompiledMatcherRoutesCacheable;
use Ixocreate\Cms\Cacheable\PageCacheable;
use Ixocreate\Cms\Cacheable\PageVersionCacheable;
use Ixocreate\Cms\Cacheable\RouteCollectionCacheable;
use Ixocreate\Cms\Cacheable\SitemapCacheable;
use Ixocreate\Cms\Cacheable\StructureCacheable;
use Ixocreate\Cms\Cacheable\StructureItemCacheable;

/** @var CacheConfigurator $cache */
$cache->addCacheable(CompiledGeneratorRoutesCacheable::class);
$cache->addCacheable(CompiledMatcherRoutesCacheable::class);
$cache->addCacheable(PageCacheable::class);
$cache->addCacheable(PageVersionCacheable::class);
$cache->addCacheable(RouteCollectionCacheable::class);
$cache->addCacheable(SitemapCacheable::class);
$cache->addCacheable(StructureCacheable::class);
$cache->addCacheable(StructureItemCacheable::class);

$cache->addCache('cms', new Chain(['cms_tmp', 'cms_store']));
$cache->addCache('cms_tmp', new InMemory());
$cache->addCache('cms_store', new Filesystem('data/cache/'));
