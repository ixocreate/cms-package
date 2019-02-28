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
use Ixocreate\Cms\Action\Page\CopyAction;
use Ixocreate\Cms\Action\Page\CreateAction;
use Ixocreate\Cms\Action\Page\DeleteAction;
use Ixocreate\Cms\Action\Page\DetailAction;
use Ixocreate\Cms\Action\Page\IndexAction;
use Ixocreate\Cms\Action\Page\IndexFlatAction;
use Ixocreate\Cms\Action\Page\IndexSubSitemapAction;
use Ixocreate\Cms\Action\Page\ListAction;
use Ixocreate\Cms\Action\Page\MoveAction;
use Ixocreate\Cms\Action\Page\UpdateAction;
use Ixocreate\Cms\Action\PageVersion\ReplaceAction;
use Ixocreate\Cms\Action\Preview\PreviewAction;
use Ixocreate\Cms\Action\Seo\RobotsAction;
use Ixocreate\Cms\Action\Seo\SitemapAction;

$pipe->segmentPipe(AdminConfig::class)(function(PipeConfigurator $pipe) {
    $pipe->segment('/api')( function(PipeConfigurator $pipe) {
        $pipe->group("admin.authorized")(function (GroupPipeConfigurator $group) {
            $group->get('/page/{id}', DetailAction::class, 'admin.api.page.detail');
            $group->get('/page/{pageId}/version', \Ixocreate\Cms\Action\Page\Version\IndexAction::class, 'admin.api.page.version.index');
            $group->post('/page/{pageId}', \Ixocreate\Cms\Action\Page\Version\CreateAction::class, 'admin.api.page.version.create');
            $group->get('/page/{pageId}/version/{id}', \Ixocreate\Cms\Action\Page\Version\DetailAction::class, 'admin.api.page.version.detail');
            $group->get('/page/index', IndexAction::class, 'admin.api.page.index');
            $group->get('/page/list', ListAction::class, 'admin.api.page.list');
            $group->get('/page/sub/index/{handle}', IndexSubSitemapAction::class, 'admin.api.page.indexSub');
            $group->get('/page/flat/index/{handle}', IndexFlatAction::class, 'admin.api.page.indexFlat');
            $group->get('/page/available-page-types[/{parentSitemapId}]', AvailablePageTypesAction::class, 'admin.api.page.availablePageTypes');
            $group->post('/page/move', MoveAction::class, "admin.api.page.move");
            $group->post('/page/copy', CopyAction::class, "admin.api.page.copy");
            $group->patch('/page/{id}', UpdateAction::class, "admin.api.page.pageUpdate");
            $group->delete('/page/{id}', DeleteAction::class, 'admin.api.page.delete');

            $group->post('/page/create', CreateAction::class, 'admin.api.page.create');
            $group->post('/page/add', AddAction::class, 'admin.api.page.add');
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