<?php
namespace KiwiSuite\Cms\Command\Page;

use KiwiSuite\Cms\Config\Config;
use KiwiSuite\Cms\Entity\Navigation;
use KiwiSuite\Cms\Repository\NavigationRepository;
use KiwiSuite\Cms\Repository\PageRepository;
use KiwiSuite\CommandBus\Command\AbstractCommand;
use KiwiSuite\CommandBus\CommandBus;
use KiwiSuite\Contract\CommandBus\CommandInterface;
use KiwiSuite\Contract\Filter\FilterableInterface;
use KiwiSuite\Contract\Validation\ValidatableInterface;
use KiwiSuite\Contract\Validation\ViolationCollectorInterface;
use Ramsey\Uuid\Uuid;

final class UpdateCommand extends AbstractCommand implements CommandInterface, ValidatableInterface, FilterableInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;
    /**
     * @var CommandBus
     */
    private $commandBus;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var NavigationRepository
     */
    private $navigationRepository;

    /**
     * CreateCommand constructor.
     * @param PageRepository $pageRepository
     * @param CommandBus $commandBus
     * @param Config $config
     * @param NavigationRepository $navigationRepository
     */
    public function __construct(
        PageRepository $pageRepository,
        CommandBus $commandBus,
        Config $config,
        NavigationRepository $navigationRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->commandBus = $commandBus;
        $this->config = $config;
        $this->navigationRepository = $navigationRepository;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function execute(): bool
    {
        $page = $this->pageRepository->find($this->dataValue("pageId"));

        $updated = false;
        if (!empty($this->dataValue('name'))) {
            $updated = true;
            $page = $page->with("name", $this->dataValue('name'));
        }

        if (!empty($this->dataValue('publishedFrom'))) {
            $updated = true;
            $page = $page->with("publishedFrom", $this->dataValue('publishedFrom'));
        }

        if (!empty($this->dataValue('publishedUntil'))) {
            $updated = true;
            $page = $page->with("publishedUntil", $this->dataValue('publishedUntil'));
        }

        if (!empty($this->dataValue('status'))) {
            $updated = true;
            $page = $page->with("status", $this->dataValue('status'));
        }

        if (!empty($this->dataValue('slug'))) {
            $this->commandBus->command(SlugCommand::class, [
                'name' => (string) $this->dataValue('slug'),
                'pageId' => (string) $page->id()
            ]);
        }

        if (!empty($this->dataValue('navigation'))) {
            $queryBuilder = $this->navigationRepository->createQueryBuilder();
            $queryBuilder->delete(Navigation::class, "nav")
                ->where("nav.pageId = :pageId")
                ->setParameter("pageId", (string) $page->id());
            $queryBuilder->getQuery()->execute();

            foreach ($this->dataValue('navigation') as $nav) {
                $navigationEntity = new Navigation([
                    'id' => Uuid::uuid4()->toString(),
                    'pageId' => (string) $page->id(),
                    'navigation' => $nav,
                ]);

                $this->navigationRepository->save($navigationEntity);
            }
        }

        if ($updated === true) {
            $page = $page->with('updatedAt', new \DateTime());
            $this->pageRepository->save($page);
        }

        return true;
    }

    public static function serviceName(): string
    {
        return 'cms.page-update';
    }

    public function validate(ViolationCollectorInterface $violationCollector): void
    {
        if (empty($this->dataValue("pageId"))) {
            $violationCollector->add("page", "invalid_pageId");
        } else {
            $page = $this->pageRepository->find($this->dataValue("pageId"));
            if (empty($page)) {
                $violationCollector->add("page", "invalid_pageId");
            }
        }

        if (!empty($this->dataValue("status"))) {
            if (!in_array($this->dataValue("status"), ['online', 'offline'])) {
                $violationCollector->add("status", "invalid_status");
            }
        }

        if (!empty($this->dataValue("navigation")) && !is_array($this->dataValue("navigation"))) {
            $violationCollector->add("navigation", "invalid_navigation");
        }
    }

    public function filter(): FilterableInterface
    {
        $newData = [];
        $newData['pageId'] = $this->dataValue('pageId');
        $newData['name'] = $this->dataValue('name');
        $newData['publishedFrom'] = $this->dataValue('publishedFrom');
        $newData['publishedUntil'] = $this->dataValue('publishedUntil');
        $newData['status'] = $this->dataValue('status');
        $newData['slug'] = $this->dataValue('slug');
        $newData['navigation'] = null;

        if (!empty($this->dataValue('navigation'))) {
            $newData['navigation'] = [];
            $navItems = array_map(function($nav) {
                return $nav['name'];
            }, $this->config->navigation());

            foreach ($this->dataValue("navigation") as $nav) {
                if (in_array($nav, $navItems)) {
                    $newData['navigation'][] = $nav;
                }
            }
        }


        return $this->withData($newData);
    }
}