<?php
declare(strict_types=1);

namespace KiwiSuite\Cms\Action\Frontend;

use KiwiSuite\Template\TemplateResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RenderAction implements MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $globalTemplateVars = array_merge(
            $request->getGlobalTemplateAttributes(),
            [
                'page'        => $request->getPage(),
                'pageContent' => $request->getPageVersion()->content(),
                'pageType'    => $request->getPageType(),
                'sitemap'     => $request->getSitemap(),
                'request'     => $request,
            ]
        );

        return new TemplateResponse($request->getPageType()->layout(), $request->getTemplateAttributes(),
            $globalTemplateVars);
    }
}
