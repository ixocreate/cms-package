<?php

namespace KiwiSuite\Cms\Action\Page;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Schema\Builder;
use KiwiSuite\Schema\Elements\SelectElement;
use KiwiSuite\Schema\Elements\TextElement;
use KiwiSuite\Schema\Schema;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateSchemaAction implements MiddlewareInterface
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
     * @var Builder
     */
    private $builder;

    /**
     * CreateSchemaAction constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param SitemapRepository $sitemapRepository
     * @param Builder $builder
     */
    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        SitemapRepository $sitemapRepository,
        Builder $builder
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->builder = $builder;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var PageTypeInterface $parentPageType */
        $parentPageType = null;
        if (!empty($request->getAttribute("parentSitemapId"))) {
            $sitemap = $this->sitemapRepository->find($request->getAttribute("parentSitemapId"));
            if (empty($sitemap)) {
                return new ApiErrorResponse("invalid_parentSitemapId");
            }

            $parentPageType = $this->pageTypeSubManager->get($sitemap->pageType());
        }

        $defaultPageType = null;
        $pageTypes = [];
        foreach ($this->pageTypeSubManager->getServiceManagerConfig()->getNamedServices() as $name => $pageTypeClass) {
            /** @var PageTypeInterface $pageTypeObj */
            $pageTypeObj = $this->pageTypeSubManager->get($pageTypeClass);

            if (empty($parentPageType)) {
                if ($pageTypeObj->isRoot() === false) {
                    continue;
                }

                if (empty($defaultPageType)) {
                    $defaultPageType = $name;
                }

                $pageTypes[$name] = $pageTypeObj->label();

                continue;
            }

            if (!in_array($name, $parentPageType->allowedChildren())) {
                continue;
            }

            if (empty($defaultPageType)) {
                $defaultPageType = $name;
            }

            $pageTypes[$name] =  $pageTypeObj->label();
        }

        $schema = (new Schema())->withAddedElement(
            $this->builder->create(SelectElement::class, 'pageType')
                ->withLabel("Page Type")
                ->withOptions($pageTypes)
        )->withAddedElement(
            $this->builder->create(TextElement::class, 'name')
                ->withLabel("Name")
        );
        return new ApiSuccessResponse($schema);
    }
}
