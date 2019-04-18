<?php

namespace Ixocreate\Cms;

/** @var \Ixocreate\CommandBus\CommandBusConfigurator $commandBus */

$commandBus->addCommandDirectory(__DIR__ . '/../src/Command', true);
