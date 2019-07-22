<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page\Version;

use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Command\Page\CreateVersionCommand;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\CommandBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CreateAction implements MiddlewareInterface
{
    /**
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * CreateAction constructor.
     * @param CommandBus $commandBus
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(
        CommandBus $commandBus,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository
    ) {
        $this->commandBus = $commandBus;
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @throws \Exception
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pageId = $request->getAttribute('id');
        /** @var Page $page */
        $page = $this->pageRepository->find($pageId);

        if (empty($page)) {
            return new ApiErrorResponse("invalid_page_id");
        }

        $sitemap = $this->sitemapRepository->find($page->sitemapId());
        if (empty($sitemap)) {
            return new ApiErrorResponse("invalid_page_id");
        }

        $content = [];
        if (!empty($request->getParsedBody()['content']) && \is_array($request->getParsedBody()['content'])) {
            $content = $request->getParsedBody()['content'];
        }

        $result = $this->commandBus->command(CreateVersionCommand::class, [
            'pageType' => $sitemap->pageType(),
            'pageId' => (string) $page->id(),
            'createdBy' => $request->getAttribute(User::class, null)->id(),
            'content' => $content,
            'approve' => true,
        ]);

        if ($result->isSuccessful()) {
            return new ApiSuccessResponse((string) $result->command()->uuid());
        }

        return new ApiErrorResponse('execution_error', $result->messages());
    }
}
