<?php
declare(strict_types=1);

namespace KiwiSuite\Cms;

/** @var MiddlewareConfigurator $middleware */
use KiwiSuite\ApplicationHttp\Middleware\MiddlewareConfigurator;
use KiwiSuite\Cms\Middleware\CmsMiddleware;
use KiwiSuite\Cms\Middleware\Factory\CmsMiddlewareFactory;

$middleware->addMiddleware(CmsMiddleware::class, CmsMiddlewareFactory::class);

$middleware->addDirectory(__DIR__ . '/../src/Action', true);
$middleware->addDirectory(__DIR__ . '/../src/Middleware', true);
