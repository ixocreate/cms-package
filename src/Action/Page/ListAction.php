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
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Tree\AdminItem;
use Ixocreate\Cms\Tree\AdminTreeFactory;
use Ixocreate\Cms\Tree\MutationCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListAction implements MiddlewareInterface
{
    /**
     * @var AdminTreeFactory
     */
    private $adminTreeFactory;
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * ListAction constructor.
     */
    public function __construct(AdminTreeFactory $adminTreeFactory, SitemapRepository $sitemapRepository)
    {
        $this->adminTreeFactory = $adminTreeFactory;
        $this->sitemapRepository = $sitemapRepository;
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

        $conditions = [];

        if ($pageType !== null) {
            $conditions['pageType'] = $pageType;
        }

        $items = $this->sitemapRepository->findBy($conditions, ['nestedLeft' => 'ASC']);

        $result = [];
        /** @var AdminItem $item */
        foreach ($items as $item) {
            $item = $this->adminTreeFactory->createItem((string) $item->id(), new MutationCollection());

            if ($item->hasPage($locale)) {
                $result[] = [
                    'id' => $item->page($locale)->id(),
                    'name' => $this->receiveFullName($item, $locale),
                ];
            }
        }

        return new ApiSuccessResponse($result);
    }

    /**
     * @param AdminItem $item
     * @param string $locale
     * @return string
     */
    private function receiveFullName(AdminItem $item, string $locale): string
    {
        $name = '';
        if (!empty($item->parent())) {
            $name .= $this->receiveFullName($item->parent(), $locale) . ' / ';
        }

        if (!$item->hasPage($locale)) {
            return ' --- ';
        }

        return $name . $item->page($locale)->name();
    }
}
