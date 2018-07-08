<?php
namespace App;
use KiwiSuite\Cms\Template\NavigationExtension;
use KiwiSuite\Cms\Template\PageUrlExtension;
use KiwiSuite\Template\TemplateConfigurator;

/** @var TemplateConfigurator $template */

$template->addExtension(NavigationExtension::class);
$template->addExtension(PageUrlExtension::class);
