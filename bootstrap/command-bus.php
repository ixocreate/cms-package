<?php
namespace KiwiSuite\Cms;
/** @var \KiwiSuite\CommandBus\Configurator $commandBus */

$commandBus->addCommandDirectory(__DIR__ . '/../src/Command', true);