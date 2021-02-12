<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Site\Admin\StructureLoader;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexSubSitemapAction implements MiddlewareInterface
{
    /**
     * @var StructureLoader
     */
    private $structureLoader;

    public function __construct(StructureLoader $structureLoader) {
        $this->structureLoader = $structureLoader;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handle = $request->getAttribute('handle');
        if (empty($handle)) {
            return new ApiErrorResponse('invalid_handle');
        }

        $item = $this->structureLoader->getTree($handle);

        if (empty($item)) {
            return new ApiErrorResponse('invalid_handle');
        }

        return new ApiSuccessResponse([
            'items' => [$item],
            'allowedAddingRoot' => false,
        ]);
    }
}
