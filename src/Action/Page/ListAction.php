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

class ListAction implements MiddlewareInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!\array_key_exists('locale', $request->getQueryParams())) {
            return new ApiErrorResponse('invalid locale');
        }
        $locale = $request->getQueryParams()['locale'];
        $pageType = null;
        if (!empty($request->getQueryParams()['pageType'])) {
            $pageType = $request->getQueryParams()['pageType'];
        }

        $collection = $this->container->search(function (Item $item) use ($locale, $pageType) {
            if ($pageType !== null && !$item->structureItem()->pageType() !== $pageType) {
                return false;
            }

            if (!$item->hasPage($locale)) {
                return false;
            }

            return true;
        });


        $result = [];

        /** @var Item $item */
        foreach ($collection as $item) {
            $result[] = [
                'id' => $item->structureItem()->pageId($locale),
                'name' => $this->receiveFullName($item, $locale)
            ];
        }

        return new ApiSuccessResponse($result);
    }

    /**
     * @param Item $item
     * @param string $locale
     * @return string
     * @throws \Exception
     */
    private function receiveFullName(Item $item, string $locale): string
    {
        $name = '';
        if (!empty($item->parent())) {
            $name .= $this->receiveFullName($item->parent(), $locale) . ' / ';
        }

        if (!$item->hasPage($locale)) {
            return ' --- ';
        }

        return $name . $item->structureItem()->pageData($locale)['name'];
    }
}
