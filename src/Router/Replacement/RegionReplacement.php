<?php
declare(strict_types=1);
namespace Ixocreate\Package\Cms\Router\Replacement;

use Ixocreate\Package\Cms\Router\RouteSpecification;
use Ixocreate\Package\Cms\Router\RoutingItem;
use Ixocreate\Package\Intl\LocaleManager;

final class RegionReplacement implements ReplacementInterface
{
    /**
     * @var LocaleManager
     */
    private $localeManager;

    /**
     * LangReplacement constructor.
     * @param LocaleManager $localeManager
     */
    public function __construct(LocaleManager $localeManager)
    {
        $this->localeManager = $localeManager;
    }

    /**
     * @return int
     */
    public function priority(): int
    {
        return 1;
    }

    /**
     * @param RouteSpecification $routeSpecification
     * @param string $locale
     * @param RoutingItem $item
     * @return RouteSpecification
     */
    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $item
    ): RouteSpecification {
        foreach ($routeSpecification->uris() as $name => $uri) {
            $routeSpecification = $routeSpecification->withUri(\str_replace('${REGION}', \Locale::getRegion($locale), $uri), $name);
        }

        return $routeSpecification;
    }
}
