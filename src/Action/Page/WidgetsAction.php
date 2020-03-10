<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Admin\Widget\WidgetCollector;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\AboveEditWidgetInterface;
use Ixocreate\Cms\PageType\BelowEditWidgetInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WidgetsAction implements MiddlewareInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * WidgetsAction constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository
    ) {

        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $collector = new WidgetCollector();

        $user = $request->getAttribute(User::class);

        /** @var Page $page */
        $page = $this->pageRepository->find($request->getAttribute('id'));
        if (empty($page)) {
            return new ApiErrorResponse('invalid_id');
        }
        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());
        if (empty($sitemap)) {
            return new ApiErrorResponse('invalid_id');
        }

        $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

        $position = $request->getAttribute('position');

        if ($position === 'above' && $pageType instanceof AboveEditWidgetInterface) {
            $pageType->receiveAboveEditWidgets($user, $collector, $page);
        }
        if ($position === 'below' && $pageType instanceof BelowEditWidgetInterface) {
            $pageType->receiveBelowEditWidgets($user, $collector, $page);
        }

        return new ApiSuccessResponse(['items' => $collector->widgets()]);
    }
}
