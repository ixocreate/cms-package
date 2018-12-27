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

class ListAction implements MiddlewareInterface
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
        if (!\array_key_exists('locale', $request->getQueryParams())) {
            return new ApiErrorResponse("invalid locale");
        }
        $locale = $request->getQueryParams()['locale'];
        $result = [];

        $iterator = new \RecursiveIteratorIterator($this->builder->build(), \RecursiveIteratorIterator::SELF_FIRST);
        /** @var Item $item */
        foreach ($iterator as $item) {
            if (\array_key_exists($locale, $item->pages())) {
                $result[] = [
                    'id' => $item->pages()[$locale]['page']->id(),
                    'name' => $this->receiveName($item, $locale),
                ];
            }
        }
        return new ApiSuccessResponse($result);
    }

    private function receiveName(Item $item, string $locale): string
    {
        $name = "";
        if (!empty($item->parent())) {
            $name = $this->receiveName($item->parent(), $locale) . ' / ';
        }

        if (!\array_key_exists($locale, $item->pages())) {
            return " --- ";
        }

        return $name . $item->pages()[$locale]['page']->name();
    }
}
