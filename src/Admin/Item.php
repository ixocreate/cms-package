<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Admin;

use Ixocreate\Cms\PageType\PageTypeSubManager;
use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Router\CmsRouter;
use Ixocreate\Cms\Tree\AbstractItem;
use Ixocreate\Cms\Tree\FactoryInterface;
use Ixocreate\Cms\Tree\FilterManager;
use Ixocreate\Cms\Tree\Structure\StructureItem;
use Ixocreate\Intl\LocaleManager;
use JsonSerializable;

final class Item extends AbstractItem implements JsonSerializable
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * @var CmsRouter
     */
    private $cmsRouter;

    public function __construct(
        StructureItem $structureItem,
        FactoryInterface $factory,
        PageTypeSubManager $pageTypeSubManager,
        FilterManager $filterManager,
        LocaleManager $localeManager,
        CmsRouter $cmsRouter,
        array $filter = []
    ) {
        parent::__construct($structureItem, $factory, $pageTypeSubManager, $filterManager, $filter);
        $this->localeManager = $localeManager;
        $this->cmsRouter = $cmsRouter;
    }

    public function jsonSerialize()
    {
        $pageType = $this->pageType();

        $pages = [];
        foreach ($this->localeManager->all() as $locale) {
            $locale = $locale['locale'];

            if (!$this->hasPage($locale)) {
                continue;
            }

            $page = $this->page($locale);
            $pages[$locale] = [
                'page' => $page->toPublicArray(),
                'url' => $this->cmsRouter->fromPage($page),
                'isOnline' => $this->isOnline($locale),
            ];
        }

        return [
            'sitemap' => $this->sitemap()->toPublicArray(),
            'pages' => $pages,
            'handle' => $this->handle(),
            'childrenAllowed' => !$pageType instanceof TerminalPageTypeInterface,
            'pageType' => [
                'label' => $pageType->label(),
                'allowedChildren' => $pageType->allowedChildren(),
                'isRoot' => $pageType instanceof RootPageTypeInterface,
                'name' => $pageType::serviceName(),
                'terminal' => $pageType instanceof TerminalPageTypeInterface,
            ],
            'children' => $this->below(),
        ];
    }
}
