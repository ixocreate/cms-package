<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Middleware;

use Ixocreate\Cms\Package\Request\CmsRequest;
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
