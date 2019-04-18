<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Router\Replacement;

use Ixocreate\Cms\Package\Router\RouteSpecification;
use Ixocreate\Cms\Package\Router\RoutingItem;
use Ixocreate\Application\Uri\ApplicationUri;

final class UriReplacement implements ReplacementInterface
{
    /**
     * @var ApplicationUri
     */
    private $projectUri;

    /**
     * UriReplacement constructor.
     *
     * @param ApplicationUri $projectUri
     */
    public function __construct(ApplicationUri $projectUri)
    {
        $this->projectUri = $projectUri;
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
            if (!empty(\preg_match('/\${URI:([a-z0-9-_]*)}/i', $uri, $matches))) {
                $projectUri = $this->projectUri->getPossibleUri($matches[1]);
                $routeSpecification = $routeSpecification->withUri(\preg_replace('/\${URI:([a-z0-9-_]*)}/i', $projectUri, $uri), $name);
            }
        }



        return $routeSpecification;
    }
}
