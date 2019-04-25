<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Site\Admin;

interface AdminSearchInterface
{
    /**
     * @param AdminItem $item
     * @param array $params
     * @return bool
     */
    public function search(AdminItem $item, array $params = []): bool;
}
