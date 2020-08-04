<?php

declare(strict_types=1);

namespace Ixocreate\Cms\Command\Page;

use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Event\PageEvent;
use Ixocreate\Cms\Repository\OldRedirectRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Event\EventDispatcher;
use Ixocreate\Filter\FilterableInterface;
use Ixocreate\Validation\ValidatableInterface;
use Ixocreate\Validation\Violation\ViolationCollectorInterface;

class DeletePageCommand extends AbstractCommand implements ValidatableInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var OldRedirectRepository
     */
    private $oldRedirectRepository;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(
        PageRepository $pageRepository,
        PageVersionRepository $pageVersionRepository,
        SitemapRepository $sitemapRepository,
        OldRedirectRepository $oldRedirectRepository,
        EventDispatcher $eventDispatcher
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->oldRedirectRepository = $oldRedirectRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public static function serviceName(): string
    {
        return 'cms.page-delete';
    }

    public function execute(): bool
    {
        /** @var Page $page */
        $page = $this->pageRepository->find($this->dataValue('id'));
        if (empty($page)) {
            return false;
        }

        $pageVersions = $this->pageVersionRepository->findBy(['pageId' => $page->id()]);
        foreach ($pageVersions as $pageVersion) {
            $this->pageVersionRepository->remove($pageVersion);
        }

        $pageRedirects = $this->oldRedirectRepository->findBy(['pageId' => $page->id()]);
        foreach ($pageRedirects as $pageRedirect) {
            $this->oldRedirectRepository->remove(($pageRedirect));
        }

        $this->pageRepository->remove($page);

        if ($this->pageRepository->count(['sitemapId' => $page->sitemapId()]) === 0) {
            $sitemap = $this->sitemapRepository->find($page->sitemapId());
            $this->sitemapRepository->remove($sitemap);
        }

        $this->eventDispatcher->dispatch(PageEvent::PAGE_DELETE, new PageEvent($page));

        return true;
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        if (empty($this->dataValue('id'))) {
            $violationCollector->add('page', 'invalid_id');
        } else {
            $page = $this->pageRepository->find($this->dataValue('id'));
            if (empty($page)) {
                $violationCollector->add('page', 'invalid_id');
            }
        }
    }
}
