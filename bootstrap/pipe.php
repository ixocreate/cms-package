<?php
declare(strict_types=1);

namespace Ixocreate\Package\Cms;

/** @var PipeConfigurator $pipe */
use Ixocreate\Package\Admin\Config\AdminConfig;
use Ixocreate\Package\Admin\Middleware\Api\AuthorizationGuardMiddleware;
use Ixocreate\Package\Admin\Middleware\Api\SessionDataMiddleware;
use Ixocreate\Package\Admin\Middleware\Api\UserMiddleware;
use Ixocreate\Application\Http\Pipe\GroupPipeConfigurator;
use Ixocreate\Application\Http\Pipe\PipeConfigurator;
use Ixocreate\Application\Http\Pipe\RouteConfigurator;
use Ixocreate\Package\Cms\Action\Page\AddAction;
use Ixocreate\Package\Cms\Action\Page\AvailablePageTypesAction;
use Ixocreate\Package\Cms\Action\Page\CopyAction as CopyPageAction;
use Ixocreate\Package\Cms\Action\Page\CreateAction;
use Ixocreate\Package\Cms\Action\Page\CreateAliasPageAction;
use Ixocreate\Package\Cms\Action\Page\DeleteAction;
use Ixocreate\Package\Cms\Action\Page\DeleteAliasPageAction;
use Ixocreate\Package\Cms\Action\Page\DetailAction;
use Ixocreate\Package\Cms\Action\Page\IndexFlatAction;
use Ixocreate\Package\Cms\Action\Page\IndexSubSitemapAction;
use Ixocreate\Package\Cms\Action\Page\ListAction;
use Ixocreate\Package\Cms\Action\Page\ShowAliasPageAction;
use Ixocreate\Package\Cms\Action\Page\UpdateAction;
use Ixocreate\Package\Cms\Action\Preview\PreviewAction;
use Ixocreate\Package\Cms\Action\Seo\RobotsAction;
use Ixocreate\Package\Cms\Action\Seo\SitemapAction;
use Ixocreate\Package\Cms\Action\Sitemap\IndexAction;
use Ixocreate\Package\Cms\Action\Sitemap\MoveAction;
use Ixocreate\Package\Cms\Action\Sitemap\CopyAction as SitemapCopyAction;

$pipe->segmentPipe(AdminConfig::class)(function(PipeConfigurator $pipe) {
    $pipe->segment('/api')( function(PipeConfigurator $pipe) {
        $pipe->group("admin.authorized")(function (GroupPipeConfigurator $group) {

            $group->get('/page/{id}', DetailAction::class, 'admin.api.page.detail');
            $group->post('/page/{id}', \Ixocreate\Package\Cms\Action\Page\Version\CreateAction::class, 'admin.api.page.version.create');
            $group->patch('/page/{id}', UpdateAction::class, "admin.api.page.pageUpdate");
            $group->delete('/page/{id}', DeleteAction::class, 'admin.api.page.delete');
            $group->get('/page/{pageId}/version', \Ixocreate\Package\Cms\Action\Page\Version\IndexAction::class, 'admin.api.page.version.index');
            $group->get('/page/{pageId}/version/{id}', \Ixocreate\Package\Cms\Action\Page\Version\DetailAction::class, 'admin.api.page.version.detail');

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
            $group->post('/sitemap/copy', SitemapCopyAction::class, "admin.api.sitemap.copy");

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
