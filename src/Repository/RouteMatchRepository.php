<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Repository;

use Ixocreate\Cms\Entity\RouteMatch;
use Ixocreate\Database\Repository\AbstractRepository;

final class RouteMatchRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return RouteMatch::class;
    }
}
