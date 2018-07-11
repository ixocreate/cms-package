<?php
namespace KiwiSuite\Cms\Console;

use KiwiSuite\Cms\Entity\Sitemap;
use KiwiSuite\Cms\Repository\SitemapRepository;
use KiwiSuite\Contract\Command\CommandInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RepairNestedSetCommand extends Command implements CommandInterface
{
    /**
     * @var SitemapRepository
     */
    private $sitemapRepository;

    public function __construct(SitemapRepository $sitemapRepository)
    {
        parent::__construct(self::getCommandName());

        $this->sitemapRepository = $sitemapRepository;
    }

    public static function getCommandName()
    {
        return 'test:test';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->sitemapRepository->findAll();

        $flat = [];

        foreach ($result as $sitemap) {
            $flat[(string) $sitemap->id()] = [
                'sitemap' => $sitemap,
                'children' => []
            ];
        }
        $tree = [];

        foreach ($flat as &$item) {
            /** @var Sitemap $sitemap */
            $sitemap = $item['sitemap'];
            if ($sitemap->parentId() !== null) {

                $parent =& $flat[(string) $sitemap->parentId()];
                $parent['children'][] =& $item;

                continue;
            }

            $tree[] =& $item;
        }

        $this->walkRecursive($tree, 0);
    }

    private function walkRecursive(array $items, int $nested)
    {
        foreach ($items as $item) {
            $nested++;
            /** @var Sitemap $sitemap */
            $sitemap = $item['sitemap'];
            $sitemap = $sitemap->with('nestedLeft', $nested);
            $nested = $this->walkRecursive($item['children'], $nested) + 1;
            $sitemap = $sitemap->with('nestedRight', $nested);

            $this->sitemapRepository->save($sitemap);
        }

        return $nested;
    }
}
