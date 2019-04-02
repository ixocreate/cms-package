<?php
declare(strict_types=1);

namespace Ixocreate\Cms;

/** @var PipeConfigurator $pipe */
use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Admin\Middleware\Api\AuthorizationGuardMiddleware;
use Ixocreate\Admin\Middleware\Api\SessionDataMiddleware;
use Ixocreate\Admin\Middleware\Api\UserMiddleware;
use Ixocreate\ApplicationHttp\Pipe\GroupPipeConfigurator;
use Ixocreate\ApplicationHttp\Pipe\PipeConfigurator;
use Ixocreate\ApplicationHttp\Pipe\RouteConfigurator;
use Ixocreate\Cms\Action\Page\AddAction;
use Ixocreate\Cms\Action\Page\AvailablePageTypesAction;
use Ixocreate\Cms\Action\Page\CopyPageAction;
use Ixocreate\Cms\Action\Page\CopySitemapAction;
use Ixocreate\Cms\Action\Page\CreateAction;
use Ixocreate\Cms\Action\Page\CreateAliasPageAction;
use Ixocreate\Cms\Action\Page\DeleteAction;
use Ixocreate\Cms\Action\Page\DeleteAliasPageAction;
use Ixocreate\Cms\Action\Page\DetailAction;
use Ixocreate\Cms\Action\Page\IndexFlatAction;
use Ixocreate\Cms\Action\Page\IndexSubSitemapAction;
use Ixocreate\Cms\Action\Page\ListAction;
use Ixocreate\Cms\Action\Page\ShowAliasPageAction;
use Ixocreate\Cms\Action\Page\UpdateAction;
use Ixocreate\Cms\Action\Preview\PreviewAction;
use Ixocreate\Cms\Action\Seo\RobotsAction;
use Ixocreate\Cms\Action\Seo\SitemapAction;
use Ixocreate\Cms\Action\Sitemap\IndexAction;
use Ixocreate\Cms\Action\Sitemap\MoveAction;

$pipe->segmentPipe(AdminConfig::class)(function(PipeConfigurator $pipe) {
    $pipe->segment('/api')( function(PipeConfigurator $pipe) {
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
            $group->get('/page/flat/index/{handle}', IndexFlatAction::class, 'admin.api.page.indexFlat');
            $group->get('/page/alias/{id}', ShowAliasPageAction::class, 'admin.api.page.alias.showAll');
            $group->delete('/page/alias/delete', DeleteAliasPageAction::class, 'admin.api.page.alias.delete');
            $group->post('/page/alias/create', CreateAliasPageAction::class, 'admin.api.page.alias.create');

            $group->post('/page/create', CreateAction::class, 'admin.api.page.create');
            $group->post('/page/add', AddAction::class, 'admin.api.page.add');
            $group->post('/page/copy', CopyPageAction::class, "admin.api.page.copy");

            $group->get('/sitemap/index', IndexAction::class, 'admin.api.sitemap.index');
            $group->post('/sitemap/move', MoveAction::class, "admin.api.sitemap.move");
            $group->post('/sitemap/copy', CopySitemapAction::class, "admin.api.sitemap.copy");

            /* deprecated */
            $group->post('/page/move', MoveAction::class, "admin.api.page.move");
            $group->get('/page/index', IndexAction::class, 'admin.api.page.index');
        });
    });

    $pipe->group("cms.preview")(function (GroupPipeConfigurator $group) {
        $group->before(SessionDataMiddleware::class);
        $group->before(UserMiddleware::class);
        $group->before(AuthorizationGuardMiddleware::class);

        $group->get('/preview', PreviewAction::class, 'admin.cms.preview')(function (RouteConfigurator $routeConfigurator) {
           $routeConfigurator->enablePost();
        });
    });
});

$pipe->get('/sitemap/[{any:.*}]', SitemapAction::class, 'cms.seo.sitemap');
$pipe->get('/robots.txt', RobotsAction::class, 'cms.seo.robots');
