<?php
namespace Ixocreate\Cms\Package;
use Ixocreate\Cms\Package\Config\Client\Provider\CmsProvider;

/** @var \Ixocreate\Admin\Package\Config\AdminConfigurator $admin */
$admin->addClientProvider(CmsProvider::class);
