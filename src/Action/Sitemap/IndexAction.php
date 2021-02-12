<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Sitemap;

use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Loader\DatabaseSitemapLoader;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Site\Admin\StructureLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexAction implements MiddlewareInterface
{
    /**
     * @var StructureLoader
     */
    private $structureLoader;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var DatabaseSitemapLoader
     */
    private $databaseSitemapLoader;

    public function __construct(
        StructureLoader $structureLoader,
        PageTypeSubManager $pageTypeSubManager,
        DatabaseSitemapLoader $databaseSitemapLoader
    ) {
        $this->structureLoader = $structureLoader;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->databaseSitemapLoader = $databaseSitemapLoader;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $r = [
            'items' => $this->structureLoader->getTree(),
            'allowedAddingRoot' => (\count($this->pageTypeSubManager->allowedPageTypes($this->databaseSitemapLoader->receiveHandles())) > 0),
        ];

        return new ApiSuccessResponse($r);
    }
}
