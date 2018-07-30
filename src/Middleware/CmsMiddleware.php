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

use KiwiSuite\Cms\Request\CmsRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Stratigility\Exception\EmptyPipelineException;
use Zend\Stratigility\MiddlewarePipe;

final class CmsMiddleware implements MiddlewareInterface
{
    /**
     * @var MiddlewarePipe
     */
    private $middlewarePipe;

    /**
     * GroupMiddlewarePipe constructor.
     */
    public function __construct(MiddlewarePipe $middlewarePipe)
    {
        $this->middlewarePipe = $middlewarePipe;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $cmsRequest = new CmsRequest($request);
            return $this->middlewarePipe->process($cmsRequest, $handler);
        } catch (EmptyPipelineException $e) {
            return $handler->handle($request);
        }
    }
}
