<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Action\Page;

use Ixocreate\Package\Admin\Response\ApiErrorResponse;
use Ixocreate\Package\Admin\Response\ApiSuccessResponse;
use Ixocreate\Package\Cms\Site\Admin\Builder;
use Ixocreate\Package\Cms\Site\Admin\Item;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListAction implements MiddlewareInterface
{
    /**
     * @var Builder
     */
    private $builder;

    /**
     * ListAction constructor.
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
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

        $result = [];
        $iterator = new \RecursiveIteratorIterator($this->builder->build(), \RecursiveIteratorIterator::SELF_FIRST);
        /** @var Item $item */
        foreach ($iterator as $item) {
            if (\array_key_exists($locale, $item->pages()) && ($pageType === null || $item->pageType()::serviceName() === $pageType)) {
                $result[] = [
                    'id' => $item->pages()[$locale]['page']->id(),
                    'name' => $this->receiveName($item, $locale),
                ];
            }
        }
        return new ApiSuccessResponse($result);
    }

    /**
     * @param Item $item
     * @param string $locale
     * @return string
     */
    private function receiveName(Item $item, string $locale): string
    {
        $name = '';
        if (!empty($item->parent())) {
            $name = $this->receiveName($item->parent(), $locale) . ' / ';
        }

        if (!\array_key_exists($locale, $item->pages())) {
            return ' --- ';
        }

        return $name . $item->pages()[$locale]['page']->name();
    }
}
