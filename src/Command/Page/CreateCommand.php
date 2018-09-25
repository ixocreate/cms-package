<?php
namespace KiwiSuite\Cms\Command\Page;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\PageType\PageTypeInterface;
use KiwiSuite\Cms\PageType\PageTypeSubManager;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\CommandBus\Command\AbstractCommand;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\Contract\CommandBus\CommandInterface;
use KiwiSuite\Contract\Filter\FilterableInterface;
use KiwiSuite\Contract\Validation\ValidatableInterface;
use KiwiSuite\Contract\Validation\ViolationCollectorInterface;
use KiwiSuite\Intl\LocaleManager;

final class CreateCommand extends AbstractCommand implements CommandInterface, ValidatableInterface, FilterableInterface
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
     * CreateCommand constructor.
     * @param PageTypeSubManager $pageTypeSubManager
     * @param SitemapRepository $sitemapRepository
     * @param PageRepository $pageRepository
     * @param LocaleManager $localeManager
     * @param CommandBus $commandBus
     */
    public function __construct(
        PageTypeSubManager $pageTypeSubManager,
        SitemapRepository $sitemapRepository,
        PageRepository $pageRepository,
        LocaleManager $localeManager,
        CommandBus $commandBus
    ) {
        $this->pageTypeSubManager = $pageTypeSubManager;
        $this->sitemapRepository = $sitemapRepository;
        $this->localeManager = $localeManager;
        $this->pageRepository = $pageRepository;
        $this->commandBus = $commandBus;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute(): bool
    {
        /** @var PageTypeInterface $pageType */
        $pageType = $this->pageTypeSubManager->get($this->dataValue("pageType"));

        $sitemap = new Sitemap([
            'id' => $this->uuid(),
            'pageType' => $pageType::serviceName(),
        ]);

        if (!empty($pageType->handle())) {
            $sitemap = $sitemap->with("handle", $pageType->handle());
        }

        if (empty($this->dataValue('parentSitemapId'))) {
            $sitemap = $this->sitemapRepository->createRoot($sitemap);
        } else {
            /** @var Sitemap $parent */
            $parent = $this->sitemapRepository->find($this->dataValue('parentSitemapId'));
            $sitemap = $this->sitemapRepository->insertAsLastChild($sitemap, $parent);
        }

        $page = new Page([
            'id' => $this->uuid(),
            'sitemapId' => $sitemap->id(),
            'locale' => $this->dataValue('locale'),
            'name' => $this->dataValue('name'),
            'status' => 'offline',
            'updatedAt' => $this->createdAt(),
            'createdAt' => $this->createdAt(),
        ]);

        /** @var Page $page */
        $page = $this->pageRepository->save($page);

        $this->commandBus->command(SlugCommand::class, [
            'name' => (string) $page->name(),
            'pageId' => (string) $page->id()
        ]);

        $this->commandBus->command(CreateVersionCommand::class, [
            'pageType' => $pageType::serviceName(),
            'pageId' => (string) $page->id(),
            'createdBy' => $this->dataValue('createdBy'),
            'content' => [],
            'approve' => true,
        ]);

        return true;
    }

    public static function serviceName(): string
    {
        return 'cms.page-create';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        if (!$this->pageTypeSubManager->has($this->dataValue('pageType'))) {
            $violationCollector->add("pageType", "invalid_pageType");
        }

        if (empty($this->dataValue('name')) || !is_string($this->dataValue('name'))) {
            $violationCollector->add("name", "invalid_name");
        }

        if (!empty($this->dataValue("parentSitemapId"))) {
            if (!is_string($this->dataValue("parentSitemapId"))) {
                $violationCollector->add("parentSitemapId", "invalid_parentSitemapId");
            } else {
                $sitemap = $this->sitemapRepository->find($this->dataValue("parentSitemapId"));
                if (empty($sitemap)) {
                    $violationCollector->add("parentSitemapId", "invalid_parentSitemapId");
                }
            }
        }

        if (!$this->localeManager->has($this->dataValue("locale"))) {
            $violationCollector->add("locale", "invalid_locale");
        }
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['pageType'] = (string) $this->dataValue('pageType');
        $newData['parentSitemapId'] = $this->dataValue('parentSitemapId');
        $newData['locale'] = (string) $this->dataValue('locale');
        $newData['name'] = (string) $this->dataValue('name');
        $newData['createdBy'] = (string) $this->dataValue('createdBy');

        return $this->withData($newData);
    }
}