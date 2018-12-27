<?php
namespace Ixocreate\Cms;
use Ixocreate\Cms\Config\Client\Provider\CmsProvider;

/** @var \Ixocreate\Admin\Config\AdminConfigurator $admin */
$admin->addClientProvider(CmsProvider::class);
