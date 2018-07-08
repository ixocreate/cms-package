<?php
/**
 * kiwi-suite/cms (https://github.com/kiwi-suite/cms)
 *
 * @package kiwi-suite/cms
 * @see https://github.com/kiwi-suite/cms
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */

declare(strict_types=1);
namespace KiwiSuite\Cms\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Stratigility\Exception\EmptyPipelineException;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\MiddlewarePipeInterface;

final class CmsMiddleware implements MiddlewarePipeInterface
{
    /**
     * @var MiddlewarePipe
     */
    private $middlewarePipe;

    /**
     * GroupMiddlewarePipe constructor.
     */
    public function __construct()
    {
        $this->middlewarePipe = new MiddlewarePipe();
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $this->middlewarePipe->process($request, $handler);
        } catch (EmptyPipelineException $e) {

        }

    }

    /**
     * @param MiddlewareInterface $middleware
     */
    public function pipe(MiddlewareInterface $middleware): void
    {
        $this->middlewarePipe->pipe($middleware);
    }

    /**
     * Handle the request and return a response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            return $this->middlewarePipe->handle($request);
        } catch (EmptyPipelineException $e) {

        }
    }
}
