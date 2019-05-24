<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Frontend;

use Ixocreate\Cms\Request\CmsRequest;
use Ixocreate\Template\TemplateResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Router\RouteResult;

final class RenderAction implements MiddlewareInterface
{
    /**
     * @param CmsRequest $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $globalTemplateVars = \array_merge(
            $request->getGlobalTemplateAttributes(),
            [
                'page'        => $request->getPage(),
                'pageContent' => $request->getPageVersion()->content(),
                'pageType'    => $request->getPageType(),
                'sitemap'     => $request->getSitemap(),
                'request'     => $request,
                'routeResult' => $request->getAttribute(RouteResult::class),
            ]
        );

        return new TemplateResponse(
            $request->getPageType()->template(),
            $request->getTemplateAttributes(),
            $globalTemplateVars
        );
    }
}
