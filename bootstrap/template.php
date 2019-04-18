<?php
namespace App;
use Ixocreate\Cms\Package\Template\NavigationExtension;
use Ixocreate\Cms\Package\Template\PageUrlExtension;
use Ixocreate\Cms\Package\Template\TreeExtension;
use Ixocreate\Template\Package\TemplateConfigurator;

/** @var TemplateConfigurator $template */

$template->addExtension(NavigationExtension::class);
$template->addExtension(PageUrlExtension::class);
$template->addExtension(TreeExtension::class);

$template->addDirectory('seo', __DIR__ . '/../templates/seo');
