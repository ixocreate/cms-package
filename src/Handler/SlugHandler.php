<?php
namespace KiwiSuite\Cms\Handler;

use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\PageVersionRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Handler\HandlerInterface;
use KiwiSuite\CommandBus\Message\MessageInterface;

final class SlugHandler implements HandlerInterface
{

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * CreateSitemapHandler constructor.
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository $pageRepository
     * @param PageVersionRepository $pageVersionRepository
     */
    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function __invoke(MessageInterface $message): MessageInterface
    {

    }
}
