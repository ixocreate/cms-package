<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Action\Page;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\Criteria;
use Ixocreate\Admin\Entity\User;
use Ixocreate\Admin\Response\ApiSuccessResponse;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\PageVersion;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
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
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * AddAction constructor.
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     * @param PageVersionRepository $pageVersionRepository
     * @param PageTypeSubManager $pageTypeSubManager
     */
    public function __construct(
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        PageVersionRepository $pageVersionRepository,
        PageTypeSubManager $pageTypeSubManager
    ) {
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->pageVersionRepository = $pageVersionRepository;
        $this->pageTypeSubManager = $pageTypeSubManager;
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

        /** @var Page $page */
        $page = $this->pageRepository->save($page);

        $this->saveSlug($page, $sitemap);
        $this->savePageVersion($page, $pageType, (string) $request->getAttribute(User::class, null)->id());


        return new ApiSuccessResponse((string) $page->id());
    }

    private function saveSlug(Page $page, Sitemap $sitemap)
    {
        $slugify = new Slugify();

        $criteria = Criteria::create();
        if (empty($sitemap->parentId())) {
            $criteria->where(Criteria::expr()->isNull("parentId"));
        } else {
            $criteria->where(Criteria::expr()->eq("parentId", $sitemap->parentId()));
        }
        $result = $this->sitemapRepository->matching($criteria);
        $sitemapIds = [];
        /** @var Sitemap $item */
        foreach ($result as $item) {
            $sitemapIds[] = $item->id();
        }


        $i = 0;
        do {
            $name = $page->name();
            if ($i > 0) {
                $name .= " " . $i;
            }

            $slug = $slugify->slugify($name);

            if ($slug === $page->slug()) {
                return;
            }

            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->in('sitemapId', $sitemapIds));
            $criteria->andWhere(Criteria::expr()->eq("locale", $page->locale()));
            $criteria->andWhere(Criteria::expr()->neq("id", $page->id()));
            $criteria->andWhere(Criteria::expr()->eq("slug", $slug));

            $result = $this->pageRepository->matching($criteria);
            $found = ($result->count() > 0);
            $i++;
        } while ($found == true);

        $page = $page->with("slug", $slug);
        $this->pageRepository->save($page);
    }

    private function savePageVersion(Page $page, PageTypeInterface $pageType, $createdBy)
    {
        $content = [];

        $pageVersion = new PageVersion([
            'id' => Uuid::uuid4()->toString(),
            'pageId' => (string) $page->id(),
            'content' => [
                '__receiver__' => [
                    'receiver' => PageTypeSubManager::class,
                    'options' => [
                        'pageType' => $pageType::serviceName(),
                    ],
                ],
                '__value__' => $content,
            ],
            'createdBy' => $createdBy,
            'approvedAt' => new \DateTime(),
            'createdAt' => new \DateTime(),

        ]);
        /** @var PageVersion $pageVersion */
        $pageVersion = $this->pageVersionRepository->save($pageVersion);
    }
}
