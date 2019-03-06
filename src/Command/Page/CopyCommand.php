<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Command\Page;

use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Driver\Connection;
use Ixocreate\Admin\Response\ApiErrorResponse;
use Ixocreate\Cms\Entity\Page;
use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\PageType\PageTypeInterface;
use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Cms\Repository\PageVersionRepository;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\CommandBus\Command\AbstractCommand;
use Ixocreate\CommandBus\CommandBus;
use Ixocreate\Contract\CommandBus\CommandInterface;
use Ixocreate\Contract\Filter\FilterableInterface;
use Ixocreate\Contract\Validation\ValidatableInterface;
use Ixocreate\Contract\Validation\ViolationCollectorInterface;
use Ixocreate\Intl\LocaleManager;

final class CopyCommand extends AbstractCommand implements CommandInterface, ValidatableInterface, FilterableInterface
{
    /**
     * @var PageTypeSubManager
     */
    private $pageTypeSubManager;

    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var Connection
     */
    private $master;

    /**
     * @var PageVersionRepository
     */
    private $pageVersionRepository;

    /**
     * CreateCommand constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository $pageRepository
     * @param LocaleManager $localeManager
     * @param CommandBus $commandBus
     * @param Connection $master
     */
    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        SitemapRepository $sitemapRepository,
        PageRepository $pageRepository,
        LocaleManager $localeManager,
        CommandBus $commandBus,
        Connection $master,
        PageVersionRepository $pageVersionRepository
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->localeManager = $localeManager;
        $this->pageRepository = $pageRepository;
        $this->commandBus = $commandBus;
        $this->master = $master;
        $this->pageVersionRepository = $pageVersionRepository;
    }

    /**
     * @throws \Exception
     * @return bool
     */
    public function execute(): bool
    {
        $this->master->transactional(function () {
            /** @var Page $page */
            $originalPage = $this->pageRepository->find($this->dataValue('idFromOriginal'));
            /** @var Sitemap $sitemap */
            $sitemap = $this->sitemapRepository->find($originalPage->sitemapId());

            /** @var PageTypeInterface $pageTypeName */
            $pageTypeName = $sitemap->pageType();
            $locale = $originalPage->locale();

            $pageType = $this->pageTypeSubManager->get($pageTypeName);
            $sitemap = new Sitemap([
                'id' => $this->uuid(),
                'pageType' => $pageTypeName,
            ]);

            if (!empty($pageType->handle())) {
                $sitemap = $sitemap->with("handle", $pageType->handle());
            }

            /** @var  $parent */
            $parent = $this->sitemapRepository->find($this->dataValue('parentSitemapId'));
            /** @var  $sibling */
            $sibling = $this->sitemapRepository->find($this->dataValue('siblingSitemapId'));

            if ($parent == null && $sibling == null && $pageType->isRoot()) {
                $sitemap = $this->sitemapRepository->createRoot($sitemap);
            } elseif ($parent == null && $sibling !== null && $pageType->isRoot()) {
                $sitemap = $this->sitemapRepository->insertAsPreviousSibling($sitemap, $sibling);
            } elseif ($parent !== null && $sibling == null){
                $sitemap = $this->sitemapRepository->insertAsFirstChild($sitemap, $parent);
            } elseif ($parent !== null && $sibling !== null){
                $sitemap = $this->sitemapRepository->insertAsNextSibling($sitemap, $sibling);
            }
            $page = new Page([
                'id' => $this->uuid(),
                'sitemapId' => $sitemap->id(),
                'locale' => $locale,
                'name' => $this->dataValue('name'),
                'status' => 'offline',
                'updatedAt' => $this->createdAt(),
                'createdAt' => $this->createdAt(),
                'releasedAt' => $this->createdAt(),
            ]);

            /** @var Page $page */
            $page = $this->pageRepository->save($page);

            $this->commandBus->command(SlugCommand::class, [
                'name' => (string) $page->name(),
                'pageId' => (string) $page->id(),
            ]);

                $criteria = Criteria::create();
                $criteria->where(Criteria::expr()->eq('pageId', $this->dataValue('idFromOriginal')));
                $criteria->orderBy(['createdAt' => 'DESC']);
                $criteria->setMaxResults(1);
                $result = $this->pageVersionRepository->matching($criteria);
                $content = $result->get(0)->content();

            $this->commandBus->command(CreateVersionCommand::class, [
                'pageType' => $pageType::serviceName(),
                'pageId' => (string) $page->id(),
                'createdBy' => $this->dataValue('createdBy'),
                'content' => $content,
                'approve' => true,
            ]);
        });

        return true;
    }

    public static function serviceName(): string
    {
        return 'cms.page-create';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {


        if (empty($this->dataValue('name')) || !\is_string($this->dataValue('name'))) {
            $violationCollector->add("name", "invalid_name");
        }

        if (!empty($this->dataValue("parentSitemapId"))) {
            if (!\is_string($this->dataValue("parentSitemapId"))) {
                $violationCollector->add("parentSitemapId", "invalid_parentSitemapId");
            } else {
                $sitemap = $this->sitemapRepository->find($this->dataValue("parentSitemapId"));
                if (empty($sitemap)) {
                    $violationCollector->add("parentSitemapId", "invalid_parentSitemapId");
                }
            }
        }
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['parentSitemapId'] = $this->dataValue('parentSitemapId');
        $newData['siblingSitemapId'] = $this->dataValue('siblingSitemapId');
        $newData['name'] = (string) $this->dataValue('name');
        $newData['createdBy'] = (string) $this->dataValue('createdBy');
        $newData['idFromOriginal'] = (string) $this->dataValue('idFromOriginal');

        return $this->withData($newData);
    }
}
