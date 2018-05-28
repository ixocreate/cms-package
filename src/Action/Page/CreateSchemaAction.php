<?php

namespace KiwiSuite\Cms\Action\Page;

use KiwiSuite\Admin\Response\ApiSuccessResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CreateSchemaAction implements MiddlewareInterface
{


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {

        return new ApiSuccessResponse([
            [
                'key'             => 'pageType',
                'type'            => 'select',
                'defaultValue'    => 'page',
                'templateOptions' => [
                    'label'       => 'Page Type',
                    'placeholder' => 'Page Type',
                    'required'    => true,
                    'options'     => [
                        [
                            'label' => 'Page',
                            'value' => 'page',
                        ],
                    ],
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
