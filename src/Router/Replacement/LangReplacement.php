<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Replacement;

use Ixocreate\Cms\PageType\RootPageTypeInterface;
use Ixocreate\Cms\Router\RouteSpecification;
use Ixocreate\Cms\Router\RoutingItem;
use Ixocreate\Intl\LocaleManager;

final class LangReplacement implements ReplacementInterface
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
     * @param RoutingItem $routingItem
     * @throws \Exception
     * @return RouteSpecification
     */
    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $routingItem
    ): RouteSpecification {
        $lang = \Locale::getPrimaryLanguage($locale);

        foreach ($routeSpecification->uris() as $name => $uri) {
            $routeSpecification = $routeSpecification->withUri(\str_replace('${LANG}', $lang, $uri), $name);
        }

        foreach ($routeSpecification->uris() as $name => $uri) {
            if ($this->localeManager->defaultLocale() === $locale) {
                $routeSpecification = $routeSpecification->withUri(\str_replace('${LANG:no-default}', "", $uri), $name);
                continue;
            }

            $routeSpecification = $routeSpecification->withUri(\str_replace('${LANG:no-default}', $lang, $uri), $name);
        }

        foreach ($routeSpecification->uris() as $name => $uri) {
            if (!($routingItem->pageType() instanceof RootPageTypeInterface) || $this->localeManager->defaultLocale() !== $locale) {
                $routeSpecification = $routeSpecification->withUri(\str_replace('${LANG:no-root-default}', $lang, $uri), $name);
                continue;
            }

            $routeSpecification = $routeSpecification->withUri(\str_replace('${LANG:no-root-default}', "", $uri), $name);
            $routeSpecification = $routeSpecification->withUri(\str_replace('${LANG:no-root-default}', $lang, $uri), RouteSpecification::NAME_INHERITANCE);
        }

        return $routeSpecification;
    }
}
