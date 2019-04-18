<?php
namespace App;
use Ixocreate\Package\Cms\Template\NavigationExtension;
use Ixocreate\Package\Cms\Template\PageUrlExtension;
use Ixocreate\Package\Cms\Template\TreeExtension;
use Ixocreate\Package\Template\TemplateConfigurator;

/** @var TemplateConfigurator $template */

$template->addExtension(NavigationExtension::class);
$template->addExtension(PageUrlExtension::class);
$template->addExtension(TreeExtension::class);

$template->addDirectory('seo', __DIR__ . '/../templates/seo');
