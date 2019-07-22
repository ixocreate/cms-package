<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Sitemap;

use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Tree\AdminItem;
use Ixocreate\Cms\Tree\AdminTreeFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexAction implements MiddlewareInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var AdminTreeFactory
     */
    private $adminTreeFactory;

    /**
     * IndexAction constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        SitemapRepository $sitemapRepository,
        AdminTreeFactory $adminTreeFactory
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->adminTreeFactory = $adminTreeFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new ApiSuccessResponse([
            'items' => $this->adminTreeFactory->createRoot()->map(function (AdminItem $item) {
                if ($item->pageType() instanceof TerminalPageTypeInterface) {
                    return $item->only([]);
                }
                return $item;
            }),
            'allowedAddingRoot' => (\count($this->pageTypeSubManager->allowedPageTypes($this->sitemapRepository->receiveUsedHandles())) > 0),
        ]);
    }
}
