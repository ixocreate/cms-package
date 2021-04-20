<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Template;

use Ixocreate\Cms\Navigation\Container;
use Ixocreate\Cms\Navigation\Item;
use Ixocreate\Cms\Repository\NavigationRepository;
use Ixocreate\Cms\Repository\PageRepository;
use Ixocreate\Template\Extension\ExtensionInterface;

final class NavigationExtension implements ExtensionInterface
{
    /**
     * @var PageRepository
     */
    private $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'nav';
    }

    public function __invoke(string $navigation, int $minLevel = 0, int $maxLevel = 2, ?string $handle = null, ?string $locale = null)
    {
        $tree = $this->pageRepository->fetchNavigationTree($navigation, $minLevel, $maxLevel, $handle, $locale);
        return $this->walkRecursive($tree);
    }

    private function walkRecursive(array $items): array
    {
        $collection = [];
        foreach ($items as $arrayItem) {
            if ($arrayItem['page']->status() !== 'online') {
                continue;
            }

            $children = $this->walkRecursive($arrayItem['children']);

            $collection[] = new Item($arrayItem['page'], $arrayItem['sitemap'], $arrayItem['sitemap']->level(), $children);
        }

        return $collection;
    }
}
