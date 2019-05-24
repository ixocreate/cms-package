<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;

use Ixocreate\Application\Http\Middleware\MiddlewareConfigurator;
use Ixocreate\Cms\Middleware\Factory\CmsMiddlewareFactory;

/** @var MiddlewareConfigurator $middleware */
$middleware->addMiddleware(CmsMiddleware::class, CmsMiddlewareFactory::class);

$middleware->addDirectory(__DIR__ . '/../src/Action', true);
$middleware->addDirectory(__DIR__ . '/../src/Middleware', true);
