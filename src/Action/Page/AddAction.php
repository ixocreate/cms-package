<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Doctrine\DBAL\Driver\Connection;
use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cache\CacheInterface;
use Ixocreate\Cms\Command\Page\CreateVersionCommand;
use Ixocreate\Cms\Command\Page\SlugCommand;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\CommandBus\CommandBus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;

class AddAction implements MiddlewareInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var Connection
     */
    private $master;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * AddAction constructor.
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     * @param PageVersionRepository $pageVersionRepository
     * @param PageTypeSubManager $pageTypeSubManager
     * @param CommandBus $commandBus
     * @param Connection $master
     * @param CacheInterface $cms
     */
    public function __construct(
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        PageTypeSubManager $pageTypeSubManager,
        CommandBus $commandBus,
        Connection $master,
        CacheInterface $cms
    ) {
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->master = $master;
        $this->cache = $cms;
        $this->commandBus = $commandBus;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($data['sitemapId']);
        /** @var PageTypeInterface $pageType */
        $pageType = $this->pageTypeSubManager->get($sitemap->pageType());

        $page = new Page([
            'id' => Uuid::uuid4()->toString(),
            'sitemapId' => $data['sitemapId'],
            'locale' => $data['locale'],
            'name' => $data['name'],
            'status' => 'offline',
            'updatedAt' => new \DateTime(),
            'createdAt' => new \DateTime(),
            'releasedAt' => new \DateTime(),
        ]);

        $this->master->transactional(function () use (&$page, $sitemap, $pageType, $request) {
            /** @var Page $page */
            $page = $this->pageRepository->save($page);

            $this->cache->clear();

            $this->commandBus->command(SlugCommand::class, [
                'name' => (string) $page->name(),
                'pageId' => (string) $page->id(),
            ]);

            $createdBy = (string) $request->getAttribute(User::class, null)->id();

            $this->commandBus->command(CreateVersionCommand::class, [
                'pageType' => $pageType::serviceName(),
                'pageId' => (string) $page->id(),
                'createdBy' => $createdBy,
                'content' => [],
                'approve' => false,
            ]);
        });

        return new ApiSuccessResponse((string) $page->id());
    }
}
