<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\CommandBus;

use Ixocreate\Cms\Command\Cache;
use Ixocreate\Cms\Command\Page;
use Ixocreate\Cms\Command\Seo;
use Ixocreate\CommandBus\CommandBusConfigurator;

/** @var CommandBusConfigurator $commandBus */
$commandBus->addCommand(Cache\GenerateCacheCommand::class);
$commandBus->addCommand(Page\AddCommand::class);
$commandBus->addCommand(Page\CopyPageCommand::class);
$commandBus->addCommand(Page\CopySitemapCommand::class);
$commandBus->addCommand(Page\CreateCommand::class);
$commandBus->addCommand(Page\CreateVersionCommand::class);
$commandBus->addCommand(Page\DeletePageCommand::class);
$commandBus->addCommand(Page\SlugCommand::class);
$commandBus->addCommand(Page\UpdateCommand::class);
$commandBus->addCommand(Seo\GenerateSitemapCommand::class);
