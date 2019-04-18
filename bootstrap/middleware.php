<?php
declare(strict_types=1);

namespace Ixocreate\Package\Cms;

/** @var MiddlewareConfigurator $middleware */
use Ixocreate\Application\Http\Middleware\MiddlewareConfigurator;
use Ixocreate\Package\Cms\Middleware\CmsMiddleware;
use Ixocreate\Package\Cms\Middleware\Factory\CmsMiddlewareFactory;

$middleware->addMiddleware(CmsMiddleware::class, CmsMiddlewareFactory::class);

$middleware->addDirectory(__DIR__ . '/../src/Action', true);
$middleware->addDirectory(__DIR__ . '/../src/Middleware', true);
