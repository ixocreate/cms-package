<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Site\Admin\Builder;
use Ixocreate\Cms\Site\Admin\Item;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexFlatAction implements MiddlewareInterface
{
    /**
     * @var Builder
     */
    private $builder;

    public function __construct(
        Builder $builder
    ) {
        $this->builder = $builder;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handle = $request->getAttribute("handle");
        $item = $this->builder->build()->findOneBy(function (Item $item) use ($handle) {
            return $item->sitemap()->handle() === $handle;
        });

        if (empty($item)) {
            return new ApiErrorResponse('invalid_handle');
        }

        $children = $item->children();

        if (!empty($request->getQueryParams()['search'])) {
            $search = $request->getQueryParams()['search'];
            $children = $children->filter(function (Item $item) use ($search) {
                foreach ($item->pages() as $padeData) {
                    if (\mb_stripos($padeData['page']->name(), $search) !== false) {
                        return true;
                    }
                }

                return false;
            });
        }

        $count = $children->count();
        $children = $children->toArray();

        $offset = 0;
        $limit = 0;
        if (!empty($request->getQueryParams()['offset'])) {
            $offset = \min((int) $request->getQueryParams()['offset'], $count);
        }

        if (!empty($request->getQueryParams()['limit'])) {
            $limit = \min(25, (int) $request->getQueryParams()['limit']);
            if (empty($limit)) {
                $limit = 25;
            }
        }

        $children = \array_slice($children, $offset, $limit);

        return new ApiSuccessResponse([
            'items' => $children,
            'meta' => [
                'parentSitemapId' => $item->sitemap()->id(),
                'count' => $count,
            ],
        ]);
    }
}
