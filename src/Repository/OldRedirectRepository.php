<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Package\Cms\Repository;

use Ixocreate\Package\Cms\Entity\OldRedirect;
use Ixocreate\Package\Database\Repository\AbstractRepository;

final class OldRedirectRepository extends AbstractRepository
{
    /**
     * @return string
     */
    public function getEntityName(): string
    {
        return OldRedirect::class;
    }
}
