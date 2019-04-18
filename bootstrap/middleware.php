<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Package;

/** @var MiddlewareConfigurator $middleware */
use Ixocreate\Application\Http\Middleware\MiddlewareConfigurator;
use Ixocreate\Cms\Package\Middleware\CmsMiddleware;
use Ixocreate\Cms\Package\Middleware\Factory\CmsMiddlewareFactory;

$middleware->addMiddleware(CmsMiddleware::class, CmsMiddlewareFactory::class);

$middleware->addDirectory(__DIR__ . '/../src/Action', true);
$middleware->addDirectory(__DIR__ . '/../src/Middleware', true);
