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

/** @var CacheConfigurator $cache */
$cache->addCacheableDirectory(__DIR__ . '/../src/Cacheable');

$cache->addCache('cms', new Chain(['cms_tmp', 'cms_store']));
$cache->addCache('cms_tmp', new InMemory());
$cache->addCache('cms_store', new Filesystem('data/cache/'));
