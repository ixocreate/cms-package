<?php
namespace Ixocreate\Package\Cms;
use Ixocreate\Package\Cms\Config\Client\Provider\CmsProvider;

/** @var \Ixocreate\Package\Admin\Config\AdminConfigurator $admin */
$admin->addClientProvider(CmsProvider::class);
