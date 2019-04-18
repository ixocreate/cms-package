<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Action\Frontend;

use Ixocreate\Cms\Package\Request\CmsRequest;
use Ixocreate\Template\Package\TemplateResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
            ]
        );

        return new TemplateResponse(
            $request->getPageType()->template(),
            $request->getTemplateAttributes(),
            $globalTemplateVars
        );
    }
}
