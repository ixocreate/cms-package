<?php

namespace KiwiSuite\Cms\Action\Page;


use KiwiSuite\Admin\Response\ApiListResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Loader\DatabasePageLoader;
use KiwiSuite\Cms\Loader\DatabaseSitemapLoader;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Cms\Router\PageRoute;
use KiwiSuite\Cms\Site\Admin\Item;
use KiwiSuite\Cms\Site\Admin\ItemCollection;
use KiwiSuite\Cms\Site\Structure\Structure;
use KiwiSuite\Cms\Site\Structure\StructureBuilder;
use KiwiSuite\Contract\Resource\AdminAwareInterface;
use KiwiSuite\Contract\Resource\ResourceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexAction implements MiddlewareInterface
{
    /**
     * @var StructureBuilder
     */
    private $structureBuilder;
    /**
     * @var DatabasePageLoader
     */
    private $databasePageLoader;
    /**
     * @var DatabaseSitemapLoader
     */
    private $databaseSitemapLoader;
    /**
     * @var PageRoute
     */
    private $pageRoute;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    public function __construct(
        StructureBuilder $structureBuilder,
        DatabasePageLoader $databasePageLoader,
        DatabaseSitemapLoader $databaseSitemapLoader,
        PageRoute $pageRoute,
        PageTypeSubManager $pageTypeSubManager
    ) {
        $this->structureBuilder = $structureBuilder;
        $this->databasePageLoader = $databasePageLoader;
        $this->databaseSitemapLoader = $databaseSitemapLoader;
        $this->pageRoute = $pageRoute;
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return new ApiSuccessResponse([
            'items' => $this->fetchTree(),
            'allowedAddingRoot' => (count($this->pageTypeSubManager->allowedChildPageTypes($this->databaseSitemapLoader->receiveHandles())) > 0),
        ]);
    }

    private function fetchTree()
    {
        $items = [];

        foreach ($this->structureBuilder->build()->structure() as $item) {
            $items[] = new Item(
                $this->databaseSitemapLoader,
                $this->databasePageLoader,
                $this->pageTypeSubManager,
                $this->pageRoute,
                0,
                null,
                $item['sitemapId'],
                $item['handle'],
                $item['pages'],
                $item['navigation'],
                $item['children']
            );
        }

        return new ItemCollection($items);
    }
}
