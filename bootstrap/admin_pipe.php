<?php
declare(strict_types=1);

namespace KiwiSuite\Cms;

/** @var \KiwiSuite\Admin\Pipe\PipeConfigurator $pipe */

use KiwiSuite\Admin\Action\Api\Auth\LoginAction;
use KiwiSuite\Admin\Action\Api\Auth\LogoutAction;
use KiwiSuite\Admin\Action\Api\Auth\UserAction;
use KiwiSuite\Admin\Action\Api\Config\ConfigAction;
use KiwiSuite\Admin\Action\Api\Session\SessionAction;
use KiwiSuite\Admin\Action\Handler\HandlerAction;
use KiwiSuite\Admin\Action\IndexAction;
use KiwiSuite\Admin\Message\ChangeEmailMessage;
use KiwiSuite\Admin\Message\ChangePasswordMessage;
use KiwiSuite\Admin\Middleware\Api\AuthorizationGuardMiddleware;
use KiwiSuite\Admin\Middleware\Api\EnforceApiResponseMiddleware;
use KiwiSuite\Admin\Middleware\Api\ErrorMiddleware;
use KiwiSuite\Admin\Middleware\Api\MessageInjectorMiddleware;
use KiwiSuite\Admin\Middleware\Api\ResourceInjectorMiddleware;
use KiwiSuite\Admin\Middleware\Api\SessionDataMiddleware;
use KiwiSuite\Admin\Middleware\Api\UserMiddleware;
use KiwiSuite\Admin\Middleware\Api\XsrfProtectionMiddleware;
use KiwiSuite\Admin\Middleware\CookieInitializerMiddleware;
use KiwiSuite\ApplicationHttp\Pipe\GroupPipeConfigurator;
use KiwiSuite\ApplicationHttp\Pipe\PipeConfigurator;
use KiwiSuite\ApplicationHttp\Pipe\RouteConfigurator;
use KiwiSuite\Cms\Action\Page\CreateSchemaAction;
use KiwiSuite\Cms\Action\Page\PageTypeSchemaAction;
use KiwiSuite\Cms\Action\Page\PageVersionIndexAction;
use KiwiSuite\Cms\Action\Page\SortAction;
use KiwiSuite\Cms\Message\PageVersion\CreatePageVersion;
use KiwiSuite\CommandBus\Message\MessageInterface;
use Zend\Expressive\Helper\BodyParams\BodyParamsMiddleware;

$pipe->segment('/api', function(PipeConfigurator $pipe) {
    $pipe->pipe(EnforceApiResponseMiddleware::class);
    $pipe->pipe(ErrorMiddleware::class);
    $pipe->pipe(SessionDataMiddleware::class);
    $pipe->pipe(UserMiddleware::class);
    $pipe->pipe(XsrfProtectionMiddleware::class);
    $pipe->pipe(BodyParamsMiddleware::class);

    $pipe->pipe(ResourceInjectorMiddleware::class, PipeConfigurator::PRIORITY_POST_ROUTING);
    $pipe->pipe(MessageInjectorMiddleware::class, PipeConfigurator::PRIORITY_POST_ROUTING);


    //Authorized routes
    $pipe->group(function (GroupPipeConfigurator $group) {
        $group->before(AuthorizationGuardMiddleware::class);

        $group->post('/page/sort', SortAction::class, "admin.api.page.sort");
        $group->get('/page/version/{id}', PageVersionIndexAction::class, "admin.api.page.version.index");
        $group->post('/page/version', HandlerAction::class, "admin.api.page.createVersion", function (RouteConfigurator $routeConfigurator) {
            $routeConfigurator->addOption(MessageInterface::class, CreatePageVersion::class);
        });
        $group->get('/page/create-schema', CreateSchemaAction::class, "admin.api.page.createSchema");
        $group->get('/page/page-type-schema/{id}', PageTypeSchemaAction::class, "admin.api.page.pageTypeSchema");
    });
});
