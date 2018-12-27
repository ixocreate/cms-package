<?php
declare(strict_types=1);

namespace Ixocreate\Cms;

/** @var MiddlewareConfigurator $middleware */
use Ixocreate\ApplicationHttp\Middleware\MiddlewareConfigurator;
use Ixocreate\Cms\Middleware\CmsMiddleware;
use Ixocreate\Cms\Middleware\Factory\CmsMiddlewareFactory;

$middleware->addMiddleware(CmsMiddleware::class, CmsMiddlewareFactory::class);

$middleware->addDirectory(__DIR__ . '/../src/Action', true);
$middleware->addDirectory(__DIR__ . '/../src/Middleware', true);
