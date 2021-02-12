<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Console;

use Ixocreate\Application\Console\ConsoleConfigurator;

/** @var ConsoleConfigurator $console */
$console->addCommand(GenerateCache::class);
$console->addCommand(GenerateRouterGeneratorCacheConsole::class);
$console->addCommand(GenerateRouterMatcherCacheConsole::class);
$console->addCommand(GenerateSitemapConsole::class);
$console->addCommand(GenerateStructureCacheConsole::class);
