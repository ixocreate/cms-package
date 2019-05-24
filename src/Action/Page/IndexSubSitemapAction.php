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
use Ixocreate\Cms\Site\Admin\AdminContainer;
use Ixocreate\Cms\Site\Admin\AdminItem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexSubSitemapAction implements MiddlewareInterface
{
    /**
     * @var AdminContainer
     */
    private $adminContainer;

    public function __construct(
        AdminContainer $adminContainer
    ) {
        $this->adminContainer = $adminContainer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handle = $request->getAttribute("handle");
        $item = $this->adminContainer->findOneBy(function (AdminItem $item) use ($handle) {
            return $item->sitemap()->handle() === $handle;
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
