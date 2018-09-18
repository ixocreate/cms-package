<?php
declare(strict_types=1);

namespace KiwiSuite\Admin;

/** @var PipeConfigurator $pipe */
use KiwiSuite\Admin\Config\AdminConfig;
use KiwiSuite\ApplicationHttp\Pipe\GroupPipeConfigurator;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\Cms\Action\Page\AddAction;
use KiwiSuite\Cms\Action\Page\CopyAction;
use KiwiSuite\Cms\Action\Page\CreateAction;
use KiwiSuite\Cms\Action\Page\DeleteAction;
use KiwiSuite\Cms\Action\Page\DetailAction;
use KiwiSuite\Cms\Action\Page\IndexAction;
use KiwiSuite\Cms\Action\Page\MoveAction;
use KiwiSuite\Cms\Action\Page\UpdateAction;
use KiwiSuite\Cms\Action\PageVersion\ReplaceAction;

$pipe->segmentPipe(AdminConfig::class)(function(PipeConfigurator $pipe) {
    $pipe->segment('/api')( function(PipeConfigurator $pipe) {
        $pipe->group("admin.authorized")(function (GroupPipeConfigurator $group) {
            $group->get('/page/{id}', DetailAction::class, 'admin.api.page.detail');
            $group->get('/page/{pageId}/version', \KiwiSuite\Cms\Action\Page\Version\IndexAction::class, 'admin.api.page.version.index');
            $group->post('/page/{pageId}', \KiwiSuite\Cms\Action\Page\Version\CreateAction::class, 'admin.api.page.version.create');
            $group->get('/page/{pageId}/version/{id}', \KiwiSuite\Cms\Action\Page\Version\DetailAction::class, 'admin.api.page.version.detail');
            $group->get('/page/index', IndexAction::class, 'admin.api.page.index');
            $group->post('/page/move', MoveAction::class, "admin.api.page.move");
            $group->post('/page/copy', CopyAction::class, "admin.api.page.copy");
            $group->put('/page/{id}', UpdateAction::class, "admin.api.page.pageUpdate");
            $group->delete('/page/{id}', DeleteAction::class, 'admin.api.page.delete');

            $group->post('/page/create', CreateAction::class, 'admin.api.page.create');
            $group->post('/page/add', AddAction::class, 'admin.api.page.add');

            $group->post('/page-version/replace/{fromId}/{toId}', ReplaceAction::class, "admin.api.pageVersion.replace");
        });
    });
});


