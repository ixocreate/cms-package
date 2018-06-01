<?php

namespace KiwiSuite\Cms\Action\Page;

use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeMapping;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\SitemapRepository;
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
     * @var PageTypeMapping
     */
    private $pageTypeMapping;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * CreateSchemaAction constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param PageTypeMapping $pageTypeMapping
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(PageTypeSubManager $pageTypeSubManager, PageTypeMapping $pageTypeMapping, SitemapRepository $sitemapRepository)
    {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageTypeMapping = $pageTypeMapping;
        $this->sitemapRepository = $sitemapRepository;
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

            $parentPageType = $this->pageTypeSubManager->get($this->pageTypeMapping->getMapping()[$sitemap->pageType()]);
        }

        $defaultPageType = null;
        $pageTypes = [];
        foreach ($this->pageTypeMapping->getMapping() as $name => $pageTypeClass) {
            /** @var PageTypeInterface $pageTypeObj */
            $pageTypeObj = $this->pageTypeSubManager->get($pageTypeClass);

            if (empty($parentPageType)) {
                if ($pageTypeObj->isRoot() === false) {
                    continue;
                }

                if (empty($defaultPageType)) {
                    $defaultPageType = $name;
                }

                $pageTypes[] = [
                    'label' => $pageTypeObj->label(),
                    'value' => $name,
                ];

                continue;
            }

            if (!in_array($name, $parentPageType->allowedChildren())) {
                continue;
            }

            if (empty($defaultPageType)) {
                $defaultPageType = $name;
            }

            $pageTypes[] = [
                'label' => $pageTypeObj->label(),
                'value' => $name,
            ];
        }

        return new ApiSuccessResponse([
            [
                'key'             => 'pageType',
                'type'            => 'select',
                'defaultValue'    => $defaultPageType,
                'templateOptions' => [
                    'label'       => 'Page Type',
                    'placeholder' => 'Page Type',
                    'required'    => true,
                    'options'     => $pageTypes,
                ],
            ],
            [
                'key'             => 'name',
                'type'            => 'input',
                'templateOptions' => [
                    'label'       => 'Name',
                    'placeholder' => 'Name',
                    'required'    => true,
                ],
            ],
        ]);
    }
}
