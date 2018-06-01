<?php

namespace KiwiSuite\Cms\Action\Page;

use KiwiSuite\Admin\Response\ApiSuccessResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PageTypeSchemaAction implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
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
