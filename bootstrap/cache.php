<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Package;

use Ixocreate\Cache\Package\CacheConfigurator;
use Ixocreate\Cache\Package\Option\Chain;
use Ixocreate\Cache\Package\Option\Filesystem;
use Ixocreate\Cache\Package\Option\InMemory;

/** @var CacheConfigurator $cache */
$cache->addCacheableDirectory(__DIR__ . '/../src/Cacheable');

$cache->addCache('cms', new Chain(['cms_tmp', 'cms_store']));
$cache->addCache('cms_tmp', new InMemory());
$cache->addCache('cms_store', new Filesystem('data/cache/'));
