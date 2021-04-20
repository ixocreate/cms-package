<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

use Ixocreate\Template\TemplateConfigurator;

/** @var TemplateConfigurator $template */
$template->addExtension(\Ixocreate\Cms\Template\NavigationExtension::class);
$template->addExtension(\Ixocreate\Cms\Template\PageUrlExtension::class);
$template->addExtension(\Ixocreate\Cms\Template\PageContentExtension::class);
$template->addExtension(\Ixocreate\Cms\Template\TreeExtension::class);

$template->addDirectory('seo', __DIR__ . '/../templates/seo');
