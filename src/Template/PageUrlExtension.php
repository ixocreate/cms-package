<?php
namespace KiwiSuite\Cms\Template;

use KiwiSuite\Cms\Entity\Page;
use KiwiSuite\Cms\Router\PageRoute;
use KiwiSuite\Contract\Template\ExtensionInterface;

final class PageUrlExtension implements ExtensionInterface
{


    /**
     * @var PageRoute
     */
    private $pageRoute;

    /**
     * PageUrlExtension constructor.
     * @param PageRoute $pageRoute
     */
    public function __construct(PageRoute $pageRoute)
    {
        $this->pageRoute = $pageRoute;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'pageUrl';
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * @param Page  $page
     * @param array $params
     * @return string
     */
    public function fromPage(Page $page, array $params = [], ?string $locale = null): string
    {
        return $this->pageRoute->fromPage($page, $params, $locale);
    }
    }
}
