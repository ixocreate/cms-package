<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Sitemap;

use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Admin\Container;
use Ixocreate\Cms\Admin\Item;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Repository\SitemapRepository;
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
     * @var Container
     */
    private $container;

    /**
     * IndexAction constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param SitemapRepository $sitemapRepository
     * @param Container $container
     */
    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        SitemapRepository $sitemapRepository,
        Container $container
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new ApiSuccessResponse([
            'items' => \iterator_to_array($this->container->filter(function (Item $item) {
                if (empty($item->parent())) {
                    return true;
                }

                return !($item->parent()->pageType() instanceof TerminalPageTypeInterface);
            })),
            'allowedAddingRoot' => (\count($this->pageTypeSubManager->allowedPageTypes($this->sitemapRepository->receiveUsedHandles())) > 0),
        ]);
    }
}
