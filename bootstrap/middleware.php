<?php
declare(strict_types=1);

namespace KiwiSuite\Cms;

/** @var MiddlewareConfigurator $middleware */
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareConfigurator;

$middleware->addDirectory(__DIR__ . '/../src/Action', true);
$middleware->addDirectory(__DIR__ . '/../src/Middleware', true);
