<?php
namespace KiwiSuite\Cms\Handler;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\Criteria;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Message\CreateSlug;
use KiwiSuite\Cms\Repository\PageRepository;
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
     * @var SitemapRepository
     */
    private $sitemapRepository;


    /**
     * SlugHandler constructor.
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(PageRepository $pageRepository, SitemapRepository $sitemapRepository)
    {
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
    }

    public function __invoke(MessageInterface $message): MessageInterface
    {
        /** @var CreateSlug $message */
        $slugify = new Slugify();
        /** @var Page $page */
        $page = $this->pageRepository->find($message->pageId());

        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());
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
                $name .= " ".$i;
            }

            $slug = $slugify->slugify($name);

            if ($slug === $page->slug()) {
                return $message;
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

        return $message;
    }
}
