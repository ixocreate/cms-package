<?php
namespace KiwiSuite\Cms;
use KiwiSuite\Cms\Config\Client\Provider\CmsProvider;

/** @var \KiwiSuite\Admin\Config\AdminConfigurator $admin */
$admin->addClientProvider(CmsProvider::class);
