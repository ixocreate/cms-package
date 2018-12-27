<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Console;

use Ixocreate\Cms\Entity\Sitemap;
use Ixocreate\Cms\Repository\SitemapRepository;
use Ixocreate\Contract\Command\CommandInterface;
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
                'children' => [],
            ];
        }
        $tree = [];

        $empty = [];

        foreach ($flat as &$item) {
            /** @var Sitemap $sitemap */
            $sitemap = $item['sitemap'];
            if ($sitemap->parentId() !== null) {
                if (empty($flat[(string) $sitemap->parentId()])) {
                    $empty[] = (string) $sitemap->id();

                    continue;
                }
                $parent =& $flat[(string) $sitemap->parentId()];
                $parent['children'][] =& $item;

                continue;
            }

            $tree[] =& $item;
        }

        $this->walkRecursive($tree, 0);

        \var_dump($empty);
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
