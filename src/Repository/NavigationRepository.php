<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Repository;

use Ixocreate\Package\Cms\Entity\Navigation;
use Ixocreate\Package\Database\Repository\AbstractRepository;

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
