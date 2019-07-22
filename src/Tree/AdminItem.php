<?php
declare(strict_types=1);

namespace Ixocreate\Cms\Tree;

use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\PageType\TerminalPageTypeInterface;
use Ixocreate\Cms\Strategy\LoaderInterface;
use Ixocreate\Intl\LocaleManager;
use JsonSerializable;

final class AdminItem extends AbstractItem implements JsonSerializable
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * AdminItem constructor.
     * @param string $id
     * @param MutationCollection $mutationCollection
     * @param TreeFactoryInterface $treeFactory
     * @param LoaderInterface $loader
     * @param LocaleManager $localeManager
     */
    public function __construct(
        string $id,
        MutationCollection $mutationCollection,
        TreeFactoryInterface $treeFactory,
        LoaderInterface $loader,
        LocaleManager $localeManager
    ) {
        parent::__construct($id, $mutationCollection, $treeFactory, $loader);
        $this->localeManager = $localeManager;
    }


    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
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
                //TODO URL
                'url' => '',
                'isOnline' => $this->isOnline($locale),
            ];
        }

        return [
            'sitemap' => $this->sitemap()->toPublicArray(),
            'pages' => $pages,
            'handle' => $this->handle(),
            'childrenAllowed' => !($pageType instanceof TerminalPageTypeInterface),
            'pageType' => [
                'label' => $pageType->label(),
                'allowedChildren' => $pageType->allowedChildren(),
                'isRoot' => $pageType instanceof RootPageTypeInterface,
                'name' => $pageType::serviceName(),
                'terminal' => $pageType instanceof TerminalPageTypeInterface
            ],
            'children' => $this->below(),
        ];
    }
}
