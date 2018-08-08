<?php

namespace KiwiSuite\Cms\Action\PageVersion;


use function FastRoute\TestFixtures\empty_options_cached;
use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Message\PageVersion\CreatePageVersion;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Cms\Resource\PageVersionResource;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\Contract\Resource\ResourceInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class ReplaceAction implements MiddlewareInterface
{

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var CreatePageVersion
     */
    private $createPageVersion;

    public function __construct(
        PageVersionRepository $pageVersionRepository,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        CommandBus $commandBus,
        CreatePageVersion $createPageVersion
    ) {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->commandBus = $commandBus;
        $this->createPageVersion = $createPageVersion;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $fromId = $request->getAttribute("fromId");
        $toId = $request->getAttribute("toId");

        /** @var Page $fromPage */
        $fromPage = $this->pageRepository->find($fromId);
        if (empty($fromPage)) {
            return new ApiErrorResponse('invalid.from');
        }

        /** @var Page $toPage */
        $toPage = $this->pageRepository->find($toId);
        if (empty($toPage)) {
            return new ApiErrorResponse('invalid.to');
        }

        /** @var Sitemap $fromSitemap */
        $fromSitemap = $this->sitemapRepository->find($fromPage->sitemapId());
        /** @var Sitemap $toSitemap */
        $toSitemap = $this->sitemapRepository->find($toPage->sitemapId());
        if ($fromSitemap->pageType() !== $toSitemap->pageType()) {
            return new ApiErrorResponse('invalid.pageType');
        }

        $body = [];
        $result = $this->pageVersionRepository->findBy(['pageId' => $fromPage->id()], ['createdAt' => 'DESC'], 1);
        if (!empty($result)) {
            /** @var PageVersion $fromPageVersion */
            $fromPageVersion = current($result);
            $body = $fromPageVersion->content()->value();
        }
        $metadata = [];
        $metadata['id'] = (string) $toPage->id();
        $metadata[User::class] = $request->getAttribute(User::class, null)->id();
        $metadata[ResourceInterface::class] = PageVersionResource::class;

        $message = $this->createPageVersion->inject($body, $metadata);
        $result = $message->validate();
        if (!$result->isSuccessful()) {
            return new ApiErrorResponse('invalid.input', $result->getErrors());
        }

        $this->commandBus->handle($message);
        return new ApiSuccessResponse([
            'id' => (string) $message->uuid()
        ]);
    }
}
