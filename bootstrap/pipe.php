<?php
declare(strict_types=1);

namespace KiwiSuite\Admin;

/** @var PipeConfigurator $pipe */
use KiwiSuite\Admin\Config\AdminConfig;
use KiwiSuite\Admin\Middleware\Api\ResourceInjectionMiddleware;
use KiwiSuite\ApplicationHttp\Pipe\GroupPipeConfigurator;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\ApplicationHttp\Pipe\RouteConfigurator;
use KiwiSuite\Cms\Action\Navigation\SaveAction;
use KiwiSuite\Cms\Action\Page\AddAction;
use KiwiSuite\Cms\Action\Page\CreateSchemaAction;
use KiwiSuite\Cms\Action\Page\FlatIndexAction;
use KiwiSuite\Cms\Action\Page\IndexAction;
use KiwiSuite\Cms\Action\Page\MoveAction;
use KiwiSuite\Cms\Action\Page\SortAction;
use KiwiSuite\Cms\Action\PageVersion\CreateAction;
use KiwiSuite\Cms\Action\PageVersion\PageVersionDetailAction;
use KiwiSuite\Cms\Action\PageVersion\ReplaceAction;

$pipe->segmentPipe(AdminConfig::class)(function(PipeConfigurator $pipe) {
    $pipe->segment('/api')( function(PipeConfigurator $pipe) {
        $pipe->group("admin.authorized")(function (GroupPipeConfigurator $group) {
            $group->get('/page/index', IndexAction::class, 'admin.api.page.index');
            $group->post('/page/move', MoveAction::class, "admin.api.page.move");

            $group->post('/page/sort', SortAction::class, "admin.api.page.sort");

            $group->get('/page/navigation/{id}', \KiwiSuite\Cms\Action\Navigation\IndexAction::class, "admin.api.page.navigation.index");
            $group->post('/page/navigation/{id}', SaveAction::class, "admin.api.page.navigation.save");

            $group->get('/page-version/{id}', PageVersionDetailAction::class, "admin.api.pageVersion.detail");
            $group->post('/page-version/{id}', CreateAction::class, "admin.api.pageVersion.create");
            $group->post('/page-version/replace/{fromId}/{toId}', ReplaceAction::class, "admin.api.pageVersion.replace");

            $group->get('/page/create-schema[/{parentSitemapId}]', CreateSchemaAction::class, "admin.api.page.createSchema");

            $group->get('/page/flat/{handle}', FlatIndexAction::class, 'admin.api.flatPages.index');
            $group->post('/page/add', AddAction::class, 'admin.api.page.add');
        });
    });
});


