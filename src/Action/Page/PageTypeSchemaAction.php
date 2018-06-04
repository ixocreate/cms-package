<?php

namespace KiwiSuite\Cms\Action\Page;

use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Admin\Schema\SchemaInstantiator;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeMapping;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageTypeSchemaAction implements MiddlewareInterface
{
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
    /**
     * @var PageTypeMapping
     */
    private $pageTypeMapping;
    /**
     * @var SchemaInstantiator
     */
    private $schemaInstantiator;

    /**
     * PageTypeSchemaAction constructor.
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     * @param PageTypeSubManager $pageTypeSubManager
     * @param PageTypeMapping $pageTypeMapping
     * @param SchemaInstantiator $schemaInstantiator
     */
    public function __construct(
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        PageTypeSubManager $pageTypeSubManager,
        PageTypeMapping $pageTypeMapping,
        SchemaInstantiator $schemaInstantiator
    ) {
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->pageTypeMapping = $pageTypeMapping;
        $this->schemaInstantiator = $schemaInstantiator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Page $page */
        $page = $this->pageRepository->find($request->getAttribute("id"));
        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());
        /** @var PageTypeInterface $pageType */
        $pageType = $this->pageTypeSubManager->get($this->pageTypeMapping->getMapping()[$sitemap->pageType()]);

        return new ApiSuccessResponse($pageType->elements($this->schemaInstantiator->createSchemaBuilder()->getForm()));

        return new ApiSuccessResponse([
            // [
            //     'key'             => 'pageType',
            //     'type'            => 'select',
            //     'defaultValue'    => 'page',
            //     'templateOptions' => [
            //         'label'       => 'Page Type',
            //         'placeholder' => 'Page Type',
            //         'required'    => true,
            //         'options'     => [
            //             [
            //                 'label' => 'Page',
            //                 'value' => 'page',
            //             ],
            //         ],
            //     ],
            // ],
            // [
            //     'key'             => 'name',
            //     'type'            => 'input',
            //     'templateOptions' => [
            //         'label'       => 'Name',
            //         'placeholder' => 'Name',
            //         'required'    => true,
            //     ],
            // ],
            [
                'key'             => 'content',
                'type'            => 'dynamic', // dynamic repeatable blocks in an array
                'templateOptions' => [
                    // 'label' => 'Content',
                    // 'btnText' => 'Add',
                ],
                'fieldArray'      => [],
                'fieldGroups'     => [
                    [
                        '_type'           => 'slideshow',
                        'templateOptions' => [
                            'label'       => 'Slideshow',
                        ],
                        'fieldGroup'      => [
                            [
                                'type'            => 'repeat',
                                'key'             => 'images',
                                'templateOptions' => [
                                    'label'   => 'Images',
                                    'btnText' => 'Add Image',
                                ],
                                'fieldArray'      => [
                                    'fieldGroup' => [
                                        [
                                            'key'             => 'title',
                                            'type'            => 'input',
                                            'templateOptions' => [
                                                'label'       => 'Title',
                                                'placeholder' => 'Title',
                                                'required'    => true,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        '_type'           => 'teaser',
                        'templateOptions' => [
                            'label' => 'Teaser',
                        ],
                        'fieldGroup'      => [
                            [
                                'key'             => 'name',
                                'type'            => 'input',
                                'templateOptions' => [
                                    'label'       => 'Name',
                                    'placeholder' => 'Name',
                                    'required'    => true,
                                ],
                            ],
                            [
                                'key'             => 'image',
                                'type'            => 'media',
                                'templateOptions' => [
                                    'label'       => 'Image',
                                    'placeholder' => 'Image',
                                    'required'    => true,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
