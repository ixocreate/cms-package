<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Admin;

use Ixocreate\Admin\AdminConfigurator;
use Ixocreate\Cms\Config\Client\Provider\CmsProvider;

/** @var AdminConfigurator $admin */
$admin->addClientProvider(CmsProvider::class);
