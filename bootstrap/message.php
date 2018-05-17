<?php
declare(strict_types=1);

namespace KiwiSuite\Cms;

/** @var MessageConfigurator $message */
use KiwiSuite\CommandBus\Message\MessageConfigurator;

$message->addDirectory( __DIR__ . '/../src/Message/', true);

