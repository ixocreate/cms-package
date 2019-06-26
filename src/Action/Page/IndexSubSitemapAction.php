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
use Ixocreate\Cms\Admin\Container;
use Ixocreate\Cms\Admin\Item;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexSubSitemapAction implements MiddlewareInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(
        Container $container
    ) {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handle = $request->getAttribute("handle");
        $item = $this->container->find(function (Item $item) use ($handle) {
            return $item->handle() === $handle;
        });

        if (empty($item)) {
            return new ApiErrorResponse('invalid_handle');
        }

        return new ApiSuccessResponse([
            'items' => [$item],
            'allowedAddingRoot' => false,
        ]);
    }
}
