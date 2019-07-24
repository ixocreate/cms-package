<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Router\Replacement;

use Ixocreate\Application\Uri\ApplicationUri;
use Ixocreate\Cms\Router\RouteSpecification;
use Ixocreate\Cms\Router\Tree\RoutingItem;

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
     */
    public function replace(
        RouteSpecification $routeSpecification,
        string $locale,
        RoutingItem $item
    ): void {
        foreach ($routeSpecification->uris() as $name => $uri) {
            if (!empty(\preg_match('/\${URI:([a-z0-9-_]*)}/i', $uri, $matches))) {
                $projectUri = $this->projectUri->getPossibleUri($matches[1]);
                $routeSpecification->addUri(\preg_replace('/\${URI:([a-z0-9-_]*)}/i', $projectUri, $uri), $name);
            }
        }
    }
}
