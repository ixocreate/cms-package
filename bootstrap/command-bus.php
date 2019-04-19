<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

/** @var \Ixocreate\CommandBus\CommandBusConfigurator $commandBus */
$commandBus->addCommandDirectory(__DIR__ . '/../src/Command', true);
