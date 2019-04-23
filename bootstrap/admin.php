<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Cms\Config\Client\Provider\CmsProvider;

/** @var \Ixocreate\Admin\AdminConfigurator $admin */
$admin->addClientProvider(CmsProvider::class);
