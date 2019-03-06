<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Command\Page\CopyCommand;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\CommandBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CopyAction implements MiddlewareInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * SortAction constructor.
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(
        SitemapRepository $sitemapRepository,
        PageTypeSubManager $pageTypeSubManager,
        PageRepository $pageRepository,
        CommandBus $commandBus)
    {
        $this->sitemapRepository = $sitemapRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageRepository = $pageRepository;
        $this->commandBus = $commandBus;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        if (!\is_array($data)) {
            return new ApiErrorResponse("invalid_data", [], 400);
        }

        $originalPage = $this->pageRepository->find($data['idFromOriginal']);
        $sitemap = $this->sitemapRepository->find($originalPage->sitemapId());
        $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

        if (empty($data['name'])){
            return new ApiErrorResponse("invalid_data", ['new name is reqired for copy a page'], 400);
        }
        if ($pageType->handle() !== null) {
            return new ApiErrorResponse("invalid_data", ['not allowed to copy a handlePage'], 400);
        }


        if (!empty($data['parentSitemapId'])){
            $parentPage = $this->pageRepository->find($data['parentSitemapId']);
            $parentSitemap = $this->sitemapRepository->find($parentPage->sitemapId());
            /** @var  $parentPageType */
            $parentPageType = $this->pageTypeSubManager->get($parentSitemap->pageType());
            $allowedChildren = $parentPageType->allowedChildren();
            $parentPageTypeName = $parentSitemap->pageType();
            if(in_array($parentPageTypeName ,$allowedChildren)){
                return new ApiErrorResponse("invalid_data", ['not allowed to copy the pageType to the location', 400]);
            }
        }
        $data['createdBy'] = (string) $request->getAttribute(User::class)->id();

        $result = $this->commandBus->command(CopyCommand::class, $data);
        if ($result->isSuccessful()) {
            return new ApiSuccessResponse((string) $result->command()->uuid());
        }

        return new ApiErrorResponse('execution_error', $result->messages());
    }
}
