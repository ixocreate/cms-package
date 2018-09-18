<?php
namespace KiwiSuite\Cms\Action\Page\Version;

use KiwiSuite\Admin\Entity\User;
use KiwiSuite\Admin\Response\ApiErrorResponse;
use KiwiSuite\Admin\Response\ApiSuccessResponse;
use KiwiSuite\Cms\Entity\PageVersion;
use KiwiSuite\Cms\Event\PageEvent;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Site\Admin\Builder;
use KiwiSuite\Cms\Site\Admin\Item;
use KiwiSuite\Event\EventDispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

final class CreateAction implements MiddlewareInterface
{
    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;
    /**
     * @var Builder
     */
    private $builder;
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * DetailAction constructor.
     * @param Builder $builder
     * @param PageVersionRepository $pageVersionRepository
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        Builder $builder,
        PageVersionRepository $pageVersionRepository,
        EventDispatcher $eventDispatcher
    ) {
        $this->pageVersionRepository = $pageVersionRepository;
        $this->builder = $builder;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pageId = $request->getAttribute("pageId");
        /** @var Item $item */
        $item = $this->builder->build()->findOneBy(function (Item $item) use ($pageId) {
            $pages = $item->pages();
            foreach ($pages as $pageItem) {
                if ((string) $pageItem['page']->id() === $pageId) {
                    return true;
                }
            }

            return false;
        });

        if (empty($item)) {
            return new ApiErrorResponse("invalid_page_id");
        }

        $page = null;
        foreach ($item->pages() as $pageItem) {
            if ((string) $pageItem['page']->id() === $pageId) {
                $page = $pageItem['page'];
            }
        }

        $content = [];
        if (!empty($request->getParsedBody()['content']) && is_array($request->getParsedBody()['content'])) {
            $content = $request->getParsedBody()['content'];
        }

        $queryBuilder = $this->pageVersionRepository->createQueryBuilder();
        $queryBuilder->update(PageVersion::class, "version")
            ->set("version.approvedAt", ":approvedAt")
            ->setParameter("approvedAt", null)
            ->where("version.pageId = :pageId")
            ->setParameter("pageId", $pageId);

        $queryBuilder->getQuery()->execute();

        $pageVersion = new PageVersion([
            'id' => Uuid::uuid4()->toString(),
            'pageId' => $pageId,
            'content' => [
                '__receiver__' => [
                    'receiver' => PageTypeSubManager::class,
                    'options' => [
                        'pageType' => $item->pageType()::serviceName()
                    ]
                ],
                '__value__' => $content,
            ],
            'createdBy' => $request->getAttribute(User::class, null)->id(),
            'approvedAt' => new \DateTime(),
            'createdAt' => new \DateTime(),

        ]);
        /** @var PageVersion $pageVersion */
        $pageVersion = $this->pageVersionRepository->save($pageVersion);

        $pageEvent = new PageEvent(
            $item->sitemap(),
            $page,
            $pageVersion,
            $item->pageType()
        );

        $this->eventDispatcher->dispatch('page-version.publish', $pageEvent);

        return new ApiSuccessResponse();
    }
}
