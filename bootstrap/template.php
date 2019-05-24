<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Template;

use Ixocreate\Template\TemplateConfigurator;

/** @var TemplateConfigurator $template */
$template->addExtension(NavigationExtension::class);
$template->addExtension(PageUrlExtension::class);
$template->addExtension(TreeExtension::class);

$template->addDirectory('seo', __DIR__ . '/../templates/seo');
