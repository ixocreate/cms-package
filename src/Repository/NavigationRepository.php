<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Package\Repository;

use Ixocreate\Cms\Package\Entity\Navigation;
use Ixocreate\Database\Package\Repository\AbstractRepository;

final class NavigationRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return Navigation::class;
    }
}
