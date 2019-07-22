<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Preview;

use Ixocreate\Admin\Entity\User;
use Ixocreate\Application\Http\Middleware\MiddlewareSubManager;
use Ixocreate\Cms\Action\Frontend\RenderAction;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\MiddlewarePageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Request\CmsRequest;
use Ixocreate\Schema\Type\SchemaType;
use Ixocreate\Schema\Type\Type;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\TextResponse;
use Zend\Expressive\MiddlewareContainer;
use Zend\Expressive\MiddlewareFactory;

final class PreviewAction implements MiddlewareInterface
{
    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * @var MiddlewareSubManager
     */
    private $middlewareSubManager;
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    public function __construct(
        PageVersionRepository $pageVersionRepository,
        MiddlewareSubManager $middlewareSubManager,
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        PageTypeSubManager $pageTypeSubManager
    ) {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->middlewareSubManager = $middlewareSubManager;
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!\array_key_exists('pageId', $request->getQueryParams())) {
            return new TextResponse("Invalid preview");
        }
        $pageId = $request->getQueryParams()['pageId'];

        /** @var Page $page */
        $page = $this->pageRepository->find($pageId);

        if (empty($page)) {
            return new TextResponse("Invalid preview");
        }

        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());
        if (empty($sitemap)) {
            return new TextResponse("Invalid preview");
        }

        $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

        $pageVersion = $this->loadPageVersion($request, $page, $pageType);

        if (empty($pageVersion)) {
            return new TextResponse("Invalid preview");
        }

        $cmsRequest = (new CmsRequest($request))
            ->withSitemap($sitemap)
            ->withPage($page)
            ->withPageType($pageType)
            ->withPageVersion($pageVersion);

        $middleware = [];
        if ($pageType instanceof MiddlewarePageTypeInterface) {
            $middleware = $pageType->middleware();
            if (empty($middleware)) {
                $middleware = [];
            }
        }

        $middleware[] = RenderAction::class;

        $middlewareFactory = new MiddlewareFactory(new MiddlewareContainer($this->middlewareSubManager));

        $pipe = $middlewareFactory->pipeline($middleware);
        return $pipe->handle($cmsRequest);
    }

    private function loadPageVersion(ServerRequestInterface $request, Page $page, PageTypeInterface $pageType): ?PageVersion
    {
        if (\array_key_exists('versionId', $request->getQueryParams())) {
            return $this->pageVersionRepository->find($request->getQueryParams()['versionId']);
        }

        if ($request->getMethod() !== "POST") {
            return null;
        }

        $body = (string) $request->getBody();

        if (empty($body)) {
            return null;
        }

        $parsedBody = [];
        \parse_str($body, $parsedBody);

        if (!\array_key_exists('preview', $parsedBody) || empty($parsedBody['preview'])) {
            return null;
        }

        $json = $parsedBody['preview'];

        $parsedBody = \json_decode($json, true);

        if (\json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        $content = [
            '__receiver__' => [
                'receiver' => PageTypeSubManager::class,
                'options' => [
                    'pageType' => $pageType::serviceName(),
                ],
            ],
            '__value__' => $parsedBody,
        ];

        return new PageVersion([
            'id' => Uuid::uuid4()->toString(),
            'pageId' => $page->id(),
            'content' => Type::create($content, SchemaType::class)->convertToDatabaseValue(),
            'createdBy' => $request->getAttribute(User::class)->id(),
            'createdAt' => new \DateTimeImmutable(),
            'approvedAt' => null,

        ]);
    }
}
