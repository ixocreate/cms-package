<?php
declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Package\Cache\CacheConfigurator;
use Ixocreate\Package\Cache\Option\Chain;
use Ixocreate\Package\Cache\Option\Filesystem;
use Ixocreate\Package\Cache\Option\InMemory;

/** @var CacheConfigurator $cache */
$cache->addCacheableDirectory(__DIR__ . '/../src/Cacheable');

$cache->addCache('cms', new Chain(['cms_tmp', 'cms_store']));
$cache->addCache('cms_tmp', new InMemory());
$cache->addCache('cms_store', new Filesystem('data/cache/'));
