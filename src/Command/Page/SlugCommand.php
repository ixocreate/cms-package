<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Command\Page;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\Criteria;
use Ixocreate\Cms\Entity\OldRedirect;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\OldRedirectRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\Filter\FilterableInterface;
use Ixocreate\Validation\ValidatableInterface;
use Ixocreate\Validation\Violation\ViolationCollectorInterface;
use Zend\Expressive\Router\Exception\RuntimeException;

final class SlugCommand extends AbstractCommand implements ValidatableInterface, FilterableInterface
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
     * @var OldRedirectRepository
     */
    private $oldRedirectRepository;

    /**
     * @var CmsRouter
     */
    private $cmsRouter;

    /**
     * SlugCommand constructor.
     *
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     * @param OldRedirectRepository $oldRedirectRepository
     * @param CmsRouter $cmsRouter
     */
    public function __construct(
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository,
        OldRedirectRepository $oldRedirectRepository,
        CmsRouter $cmsRouter
    ) {
        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
        $this->oldRedirectRepository = $oldRedirectRepository;
        $this->cmsRouter = $cmsRouter;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        /** @var Page $page */
        $page = $this->pageRepository->find($this->dataValue("pageId"));

        /** @var Sitemap $sitemap */
        $sitemap = $this->sitemapRepository->find($page->sitemapId());

        $i = 0;
        $iterationName = $this->dataValue("name");
        do {
            if ($i > 0) {
                $iterationName .= "-" . $i;
            }
            if ($iterationName === $page->slug()) {
                return true;
            }
            $sParentId = (!empty($sitemap->parentId())) ? (string)$sitemap->parentId() : null;
            $found = $this->pageRepository->slugExists(
                $sParentId,
                (string)$this->dataValue("pageId"),
                $iterationName,
                $page->locale()
            );
            $i++;
        } while ($found == true);

        if ($this->dataValue('isChange') === true) {
            $this->saveRedirectInfo($page);
        }
        $this->pageRepository->save($page->with("slug", $iterationName));
        return true;
    }

    private function saveRedirectInfo(Page $page)
    {
        try {
            /** @var Sitemap $sitemap */
            $sitemap = $this->sitemapRepository->find($page->sitemapId());

            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->gte('nestedLeft', $sitemap->nestedLeft()));
            $criteria->andWhere(Criteria::expr()->lte('nestedRight', $sitemap->nestedRight()));

            $result = $this->sitemapRepository->matching($criteria);

            $sitemapIds = [];
            /** @var Sitemap $sitemap */
            foreach ($result as $sitemap) {
                $sitemapIds[] = $sitemap->id();
            }

            $pages = $this->pageRepository->findBy(['sitemapId' => $sitemapIds, 'locale' => $page->locale()]);
            /** @var Page $pageItem */
            foreach ($pages as $pageItem) {
                $oldUrl = $this->cmsRouter->fromPage($pageItem);
                $redirect = new OldRedirect([
                    'oldUrl' => $oldUrl,
                    'pageId' => $pageItem->id(),
                    'createdAt' => new \DateTime(),
                ]);
                $this->oldRedirectRepository->save($redirect);
            }
        } catch (RuntimeException $exception) {
        }
    }

    public static function serviceName(): string
    {
        return 'cms.page-slug';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        $page = $this->pageRepository->find($this->dataValue("pageId"));
        if (empty($page)) {
            $violationCollector->add("page", "invalid_pageId");
        }

        if (empty($this->dataValue("name"))) {
            $violationCollector->add("page", "invalid_name");
        }
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['name'] = (new Slugify())->slugify((string)$this->dataValue('name', ''));
        $newData['pageId'] = $this->dataValue('pageId');
        $newData['isChange'] = (bool)$this->dataValue('isChange', false);

        return $this->withData($newData);
    }
}
