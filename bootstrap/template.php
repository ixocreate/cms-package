<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace App;

use Ixocreate\Cms\Template\NavigationExtension;
use Ixocreate\Cms\Template\PageUrlExtension;
use Ixocreate\Cms\Template\TreeExtension;
use Ixocreate\Template\TemplateConfigurator;

/** @var TemplateConfigurator $template */
$template->addExtension(NavigationExtension::class);
$template->addExtension(PageUrlExtension::class);
$template->addExtension(TreeExtension::class);

$template->addDirectory('seo', __DIR__ . '/../templates/seo');
