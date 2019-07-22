<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Tree\AdminTreeFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexSubSitemapAction implements MiddlewareInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var AdminTreeFactory
     */
    private $adminTreeFactory;

    public function __construct(
        SitemapRepository $sitemapRepository,
        AdminTreeFactory $adminTreeFactory
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->adminTreeFactory = $adminTreeFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handle = $request->getAttribute("handle");
        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->findOneBy([
            'handle' => $handle
        ]);

        if (empty($sitemap)) {
            return new ApiErrorResponse('invalid_handle');
        }

        $item = $this->adminTreeFactory->createItem((string) $sitemap->id());

        return new ApiSuccessResponse([
            'items' => [$item],
            'allowedAddingRoot' => false,
        ]);
    }
}
