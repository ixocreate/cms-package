<?php
namespace App;
use Ixocreate\Cms\Template\NavigationExtension;
use Ixocreate\Cms\Template\PageUrlExtension;
use Ixocreate\Template\TemplateConfigurator;

/** @var TemplateConfigurator $template */

$template->addExtension(NavigationExtension::class);
$template->addExtension(PageUrlExtension::class);

$template->addDirectory('seo', __DIR__ . '/../templates/seo');
