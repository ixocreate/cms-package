<?php
declare(strict_types=1);
namespace Ixocreate\Cms\Router\Replacement;

use Ixocreate\Cms\Router\RouteSpecification;
use Ixocreate\Cms\Router\RoutingItem;
use Ixocreate\ProjectUri\ProjectUri;

final class UriReplacement implements ReplacementInterface
{
    /**
     * @var ProjectUri
     */
    private $projectUri;

    /**
     * UriReplacement constructor.
     * @param ProjectUri $projectUri
     */
    public function __construct(ProjectUri $projectUri)
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
