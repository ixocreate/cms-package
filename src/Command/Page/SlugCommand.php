<?php
namespace KiwiSuite\Cms\Command\Page;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\Criteria;
use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Command\AbstractCommand;
use KiwiSuite\Contract\CommandBus\CommandInterface;
use KiwiSuite\Contract\Filter\FilterableInterface;
use KiwiSuite\Contract\Validation\ValidatableInterface;
use KiwiSuite\Contract\Validation\ViolationCollectorInterface;

final class SlugCommand extends AbstractCommand implements CommandInterface, ValidatableInterface, FilterableInterface
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
     * SlugCommand constructor.
     * @param PageRepository $pageRepository
     * @param SitemapRepository $sitemapRepository
     */
    public function __construct(
        PageRepository $pageRepository,
        SitemapRepository $sitemapRepository
    ) {

        $this->pageRepository = $pageRepository;
        $this->sitemapRepository = $sitemapRepository;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute(): bool
    {
        /** @var Page $page */
        $page = $this->pageRepository->find($this->dataValue("pageId"));

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

        $iterationName = $this->dataValue("name");
        do {
            if ($i > 0) {
                $iterationName .= "-".$i;
            }

            if ($iterationName === $page->slug()) {
                return true;
            }

            $criteria = Criteria::create();
            $criteria->where(Criteria::expr()->in('sitemapId', $sitemapIds));
            $criteria->andWhere(Criteria::expr()->eq("locale", $page->locale()));
            $criteria->andWhere(Criteria::expr()->neq("id", $page->id()));
            $criteria->andWhere(Criteria::expr()->eq("slug", $iterationName));
            $criteria->setMaxResults(1);

            $result = $this->pageRepository->matching($criteria);
            $found = ($result->count() > 0);
            $i++;
        } while ($found == true);

        $this->pageRepository->save($page->with("slug", $iterationName));

        return true;
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
        $newData['name'] = (new Slugify())->slugify((string) $this->dataValue('name', ''));
        $newData['pageId'] = $this->dataValue('pageId');

        return $this->withData($newData);
    }
}