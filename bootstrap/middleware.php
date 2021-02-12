<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms\Middleware;

use Ixocreate\Application\Http\Middleware\MiddlewareConfigurator;
use Ixocreate\Cms\Action\Frontend;
use Ixocreate\Cms\Action\Navigation;
use Ixocreate\Cms\Action\Page;
use Ixocreate\Cms\Action\Preview;
use Ixocreate\Cms\Action\Seo;
use Ixocreate\Cms\Action\Sitemap;
use Ixocreate\Cms\Middleware\Factory\CmsMiddlewareFactory;

/** @var MiddlewareConfigurator $middleware */
$middleware->addMiddleware(CmsMiddleware::class, CmsMiddlewareFactory::class);
$middleware->addMiddleware(DefaultLocaleMiddleware::class);
$middleware->addMiddleware(LoadPageContentMiddleware::class);
$middleware->addMiddleware(LoadPageMiddleware::class);
$middleware->addMiddleware(LoadPageTypeMiddleware::class);
$middleware->addMiddleware(LoadSitemapMiddleware::class);
$middleware->addMiddleware(OldUrlRedirectMiddleware::class);

$middleware->addAction(Frontend\RenderAction::class);

$middleware->addAction(Navigation\IndexAction::class);

$middleware->addAction(Page\Version\CreateAction::class);
$middleware->addAction(Page\Version\DetailAction::class);
$middleware->addAction(Page\Version\IndexAction::class);
$middleware->addAction(Page\AddAction::class);
$middleware->addAction(Page\AvailablePageTypesAction::class);
$middleware->addAction(Page\CopyAction::class);
$middleware->addAction(Page\CreateAction::class);
$middleware->addAction(Page\CreateAliasPageAction::class);
$middleware->addAction(Page\DeleteAction::class);
$middleware->addAction(Page\DeleteAliasPageAction::class);
$middleware->addAction(Page\DetailAction::class);
$middleware->addAction(Page\FlatIndexAction::class);
$middleware->addAction(Page\FlatListAction::class);
$middleware->addAction(Page\IndexSubSitemapAction::class);
$middleware->addAction(Page\ListAction::class);
$middleware->addAction(Page\ShowAliasPageAction::class);
$middleware->addAction(Page\UpdateAction::class);
$middleware->addAction(Page\WidgetsAction::class);

$middleware->addAction(Preview\PreviewAction::class);

$middleware->addAction(Seo\RobotsAction::class);
$middleware->addAction(Seo\SitemapAction::class);

$middleware->addAction(Sitemap\CopyAction::class);
$middleware->addAction(Sitemap\IndexAction::class);
$middleware->addAction(Sitemap\ListPagesAction::class);
$middleware->addAction(Sitemap\MoveAction::class);
