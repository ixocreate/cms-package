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
use Ixocreate\Intl\LocaleManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexFlatAction implements MiddlewareInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @param Container $container
     * @param LocaleManager $localeManager
     */
    public function __construct(
        Container $container,
        LocaleManager $localeManager
    ) {
        $this->container = $container;
        $this->localeManager = $localeManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handle = $request->getAttribute('handle');
        $item = $this->container->find(function (Item $item) use ($handle) {
            return $item->handle() === $handle;
        });

        if (empty($item)) {
            return new ApiErrorResponse('invalid_handle');
        }

        $children = $item->below()->flatten();

        if (!empty($request->getQueryParams()['search'])) {
            $search = $request->getQueryParams()['search'];
            $children = $children->filter(function (Item $item) use ($search) {
                foreach ($this->localeManager->all() as $locale) {
                    $locale = $locale['locale'];
                    if (!$item->hasPage($locale)) {
                        continue;
                    }

                    $pageData = $item->structureItem()->pageData($locale);
                    if (\mb_stripos($pageData['name'], $search) !== false) {
                        return true;
                    }
                }

                return false;
            });
        }

        $count = $children->count();

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

        $children = $children->slice($offset, $limit);

        return new ApiSuccessResponse([
            'items' => $children->toArray(),
            'meta' => [
                'parentSitemapId' => $item->structureItem()->sitemapId(),
                'count' => $count,
            ],
        ]);
    }
}
