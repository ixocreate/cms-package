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
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Tree\AdminItem;
use Ixocreate\Cms\Tree\AdminTreeFactory;
use Ixocreate\Cms\Tree\MutationCollection;
use Ixocreate\Collection\Collection;
use Ixocreate\Intl\LocaleManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IndexFlatAction implements MiddlewareInterface
{

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;
    /**
     * @var AdminTreeFactory
     */
    private $adminTreeFactory;
    /**
     * @var LocaleManager
     */
    private $localeManager;

    public function __construct(
        SitemapRepository $sitemapRepository,
        AdminTreeFactory $adminTreeFactory,
        LocaleManager $localeManager
    ) {
        $this->sitemapRepository = $sitemapRepository;
        $this->adminTreeFactory = $adminTreeFactory;
        $this->localeManager = $localeManager;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $handle = $request->getAttribute('handle');
        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->findOneBy([
            'handle' => $handle
        ]);

        if (empty($sitemap)) {
            return new ApiErrorResponse('invalid_handle');
        }

        $item = $this->adminTreeFactory->createItem((string) $sitemap->id(), new MutationCollection());

        $children = new Collection(
            $item->below()->flatten()->toArray()
        );

        if (!empty($request->getQueryParams()['search'])) {
            $search = $request->getQueryParams()['search'];
            $children = $children->filter(function (AdminItem $item) use ($search) {
                $check = false;
                foreach ($this->localeManager->all() as $locale) {
                    $locale = $locale['locale'];
                    if (!$item->hasPage($locale)) {
                        continue;
                    }

                    $page = $item->page($locale);
                    if (\mb_stripos($page->name(), $search) !== false) {
                        $check = true;
                        break;
                    }
                }

                return $check;
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
            'items' => \array_values($children->toArray()),
            'meta' => [
                'parentSitemapId' => $sitemap->id(),
                'count' => $count,
            ],
        ]);
    }
}
