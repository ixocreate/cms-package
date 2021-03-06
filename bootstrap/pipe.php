<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Cms;

use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Admin\Middleware\Api\AuthorizationGuardMiddleware;
use Ixocreate\Admin\Middleware\Api\SessionDataMiddleware;
use Ixocreate\Admin\Middleware\Api\UserMiddleware;
use Ixocreate\Application\Http\Pipe\GroupPipeConfigurator;
use Ixocreate\Application\Http\Pipe\PipeConfigurator;
use Ixocreate\Application\Http\Pipe\RouteConfigurator;
use Ixocreate\Cms\Action\Page\AddAction;
use Ixocreate\Cms\Action\Page\AvailablePageTypesAction;
use Ixocreate\Cms\Action\Page\CopyAction as CopyPageAction;
use Ixocreate\Cms\Action\Page\CreateAction;
use Ixocreate\Cms\Action\Page\CreateAliasPageAction;
use Ixocreate\Cms\Action\Page\DeleteAction;
use Ixocreate\Cms\Action\Page\DeleteAliasPageAction;
use Ixocreate\Cms\Action\Page\DetailAction;
use Ixocreate\Cms\Action\Page\FlatIndexAction;
use Ixocreate\Cms\Action\Page\FlatListAction;
use Ixocreate\Cms\Action\Page\IndexSubSitemapAction;
use Ixocreate\Cms\Action\Page\ListAction;
use Ixocreate\Cms\Action\Page\ShowAliasPageAction;
use Ixocreate\Cms\Action\Page\UpdateAction;
use Ixocreate\Cms\Action\Page\WidgetsAction;
use Ixocreate\Cms\Action\Preview\PreviewAction;
use Ixocreate\Cms\Action\Seo\RobotsAction;
use Ixocreate\Cms\Action\Seo\SitemapAction;
use Ixocreate\Cms\Action\Sitemap\CopyAction as SitemapCopyAction;
use Ixocreate\Cms\Action\Sitemap\IndexAction;
use Ixocreate\Cms\Action\Sitemap\ListPagesAction;
use Ixocreate\Cms\Action\Sitemap\MoveAction;

/** @var PipeConfigurator $pipe */
$pipe->segmentPipe(AdminConfig::class)(function (PipeConfigurator $pipe) {
    $pipe->segment('/api')(function (PipeConfigurator $pipe) {
        $pipe->group("admin.authorized")(function (GroupPipeConfigurator $group) {
            $group->get('/page/{id}', DetailAction::class, 'admin.api.page.detail');
            $group->post('/page/{id}', \Ixocreate\Cms\Action\Page\Version\CreateAction::class, 'admin.api.page.version.create');
            $group->patch('/page/{id}', UpdateAction::class, "admin.api.page.pageUpdate");
            $group->delete('/page/{id}', DeleteAction::class, 'admin.api.page.delete');
            $group->get('/page/{pageId}/version', \Ixocreate\Cms\Action\Page\Version\IndexAction::class, 'admin.api.page.version.index');
            $group->get('/page/{pageId}/version/{id}', \Ixocreate\Cms\Action\Page\Version\DetailAction::class, 'admin.api.page.version.detail');

            $group->get('/page/available-page-types[/{parentSitemapId}]', AvailablePageTypesAction::class, 'admin.api.page.availablePageTypes');
            $group->get('/page/list', ListAction::class, 'admin.api.page.list');
            $group->get('/page/sub/index/{handle}', IndexSubSitemapAction::class, 'admin.api.page.indexSub');
            $group->get('/page/flat/index/{handle}', FlatIndexAction::class, 'admin.api.page.indexFlat');
            $group->get('/page/flat/list/{handle}', FlatListAction::class, 'admin.api.page.listFlat');
            $group->get('/page/alias/{id}', ShowAliasPageAction::class, 'admin.api.page.alias.showAll');
            $group->get('/page/widget/{position}/{id}', WidgetsAction::class, 'admin.api.page.widgets');
            $group->delete('/page/alias/delete', DeleteAliasPageAction::class, 'admin.api.page.alias.delete');
            $group->post('/page/alias/create', CreateAliasPageAction::class, 'admin.api.page.alias.create');

            $group->post('/page/create', CreateAction::class, 'admin.api.page.create');
            $group->post('/page/add', AddAction::class, 'admin.api.page.add');
            $group->post('/page/copy', CopyPageAction::class, "admin.api.page.copy");

            $group->get('/sitemap/index', IndexAction::class, 'admin.api.sitemap.index');
            $group->post('/sitemap/move', MoveAction::class, "admin.api.sitemap.move");
            $group->post('/sitemap/copy', SitemapCopyAction::class, "admin.api.sitemap.copy");
            $group->get('/sitemap/{id}/list-pages', ListPagesAction::class, "admin.api.sitemap.listPages");

            /* deprecated */
            $group->post('/page/move', MoveAction::class, "admin.api.page.move");
            $group->get('/page/index', IndexAction::class, 'admin.api.page.index');
        });
    });

    $pipe->group("cms.preview")(function (GroupPipeConfigurator $group) {
        $group->before(SessionDataMiddleware::class);
        $group->before(UserMiddleware::class);
        $group->before(AuthorizationGuardMiddleware::class);

        $group->get('/preview', PreviewAction::class, 'admin.cms.preview')(
            function (RouteConfigurator $routeConfigurator) {
                $routeConfigurator->enablePost();
            }
        );
    });
});

$pipe->get('/sitemap/[{any:.*}]', SitemapAction::class, 'cms.seo.sitemap');
$pipe->get('/robots.txt', RobotsAction::class, 'cms.seo.robots');
