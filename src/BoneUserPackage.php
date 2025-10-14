<?php

declare(strict_types=1);

namespace Bone\User;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Console\CommandRegistrationInterface;
use Bone\Contracts\Container\FixtureProviderInterface;
use Bone\Controller\Init;
use Bone\Http\Middleware\HalEntity;
use Bone\Http\Middleware\JsonParse;
use Bone\Http\Middleware\Stack;
use Bone\I18n\I18nRegistrationInterface;
use Bone\Mail\Service\MailService;
use Bone\OAuth2\Http\Middleware\ResourceServerMiddleware;
use Bone\OAuth2\Http\Middleware\ScopeCheck;
use Bone\Paseto\PasetoService;
use Bone\Server\SiteConfig;
use Bone\Router\Router;
use Bone\Router\RouterConfigInterface;
use Bone\User\Controller\BoneUserApiController;
use Bone\User\Controller\BoneUserController;
use Bone\User\Fixtures\LoadUsers;
use Bone\User\Http\Controller\Admin\PersonAdminController;
use Bone\User\Http\Controller\Api\PersonApiController;
use Bone\User\Http\Middleware\SessionAuth;
use Bone\User\Http\Middleware\SessionAuthRedirect;
use Bone\User\View\Helper\LoginWidget;
use Bone\View\ViewEngine;
use Bone\View\ViewRegistrationInterface;
use Del\Booty\AssetRegistrationInterface;
use Del\Console\CreateUserCommand;
use Del\Console\ResetPasswordCommand;
use Del\Service\UserService;
use Del\SessionManager;
use Del\UserPackage;
use League\Route\RouteGroup;
use League\Route\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;
use Laminas\I18n\Translator\Translator;

class BoneUserPackage implements RegistrationInterface, RouterConfigInterface, I18nRegistrationInterface,
                                 AssetRegistrationInterface, ViewRegistrationInterface, CommandRegistrationInterface,
                                 FixtureProviderInterface
{
    public function addToContainer(Container $c)
    {
        $c[BoneUserController::class] = $c->factory(function (Container $c) {
            /** @var MailService $mailService */
            $mailService = $c->get(MailService::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);
            $pasetoService = $c->get(PasetoService::class);
            $defaultLayout = $c->get('default_layout');
            $adminLayout = $c->has('admin_layout') ? $c->get('admin_layout') : $defaultLayout;
            $options = [];

            if ($c->has('bone-user')) {
                $options = $c->get('bone-user');
            }

            $loginRedirectRoute = $options['loginRedirectRoute'] ?? '/user/home';
            $registrationEnabled = $options['enableRegistration'] ?? true;
            $profileRequired = $options['requireProfile'] ?? false;
            $rememberMeCookie = $options['rememberMeCookie'] ?? false;
            $controller = new BoneUserController($userService, $mailService, $loginRedirectRoute, $adminLayout, $pasetoService, $registrationEnabled, $profileRequired, $rememberMeCookie);

            return  Init::controller($controller, $c);
        });

        $c[BoneUserApiController::class] = $c->factory(function (Container $c) {
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);
            $dir = $c->get('uploads_dir');
            $img = $c->get('image_dir');
            $tmp = $c->get('temp_dir');
            $mailService = $c->get(MailService::class);

            return Init::controller(new BoneUserApiController($userService, $dir, $img, $tmp, $mailService), $c);
        });


        $c[SessionAuth::class] = $c->factory(function (Container $c) {
            /** @var SessionManager $session */
            $session = $c->get(SessionManager::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);
            /** @var PasetoService $pasetoService */
            $pasetoService = $c->get(PasetoService::class);

            return new SessionAuth($session, $userService, $pasetoService);
        });

        $c[SessionAuthRedirect::class] = $c->factory(function (Container $c) {
            /** @var SessionManager $session */
            $session = $c->get(SessionManager::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);
            /** @var PasetoService $pasetoService */
            $pasetoService = $c->get(PasetoService::class);

            return new SessionAuthRedirect($session, $userService, $pasetoService);
        });
    }

    public function getAssetFolders(): array
    {
        return [
            'bone-user' => dirname(__DIR__) . '/data/assets',
        ];
    }

    public function getTranslationsDirectory(): string
    {
        return dirname(__DIR__) . '/data/translations';
    }

    public function addViews(): array
    {
        return [
            'boneuser' => __DIR__ . '/View/BoneUser/',
            'email.user' => __DIR__ . '/View/email/',
        ];
    }

    public function addViewExtensions(Container $c): array
    {
        $userService = $c->get(UserService::class);
        $translator = $c->get(Translator::class);
        $sessionManager = $c->get(SessionManager::class);
        $uploadFolder = $c->get('uploads_dir');
        $loginWidget = new LoginWidget($userService, $translator, $sessionManager, $uploadFolder);

        return [$loginWidget];
    }

    public function addRoutes(Container $c, Router $router): Router
    {
        $router->group('/user', function (RouteGroup $route) {
            $route->map('GET', '/', [BoneUserController::class, 'indexAction']);
            $route->map('GET', '/lost-password/{email}', [BoneUserController::class, 'forgotPasswordAction']);
            $route->map('GET', '/login', [BoneUserController::class, 'loginAction']);
            $route->map('POST', '/login', [BoneUserController::class, 'loginFormAction']);
            $route->map('GET', '/logout', [BoneUserController::class, 'logoutAction']);
            $route->map('GET', '/activate/{email}/{token}', [BoneUserController::class, 'activateAction']);
            $route->map('GET', '/reset-email/{email}/{new-email}/{token}', [BoneUserController::class, 'resetEmailAction']);
            $route->map('GET', '/reset-password/{email}/{token}', [BoneUserController::class, 'resetPasswordAction']);
            $route->map('POST', '/reset-password/{email}/{token}', [BoneUserController::class, 'resetPasswordAction']);
            $route->map('GET', '/resend-activation-mail/{email}', [BoneUserController::class, 'resendActivationEmailAction']);
        });

        $canRegister = true;

        if ($c->has('bone-user')) {
            $config = $c->get('bone-user');
            $canRegister = $config['enableRegistration'] ?? true;
        }

        if ($canRegister) {
            $router->map('GET', '/user/register', [BoneUserController::class, 'registerAction']);
            $router->map('POST', '/user/register', [BoneUserController::class, 'registerAction']);
        }

        $auth = $c->get(SessionAuth::class);
        $router->map('GET', '/user/change-password', [BoneUserController::class, 'changePasswordAction'])->middleware($auth);
        $router->map('POST', '/user/change-password', [BoneUserController::class, 'changePasswordAction'])->middleware($auth);
        $router->map('GET', '/user/change-email', [BoneUserController::class, 'changeEmailAction'])->middleware($auth);
        $router->map('POST', '/user/change-email', [BoneUserController::class, 'changeEmailAction'])->middleware($auth);
        $router->map('GET', '/user/edit-profile', [BoneUserController::class, 'editProfileAction'])->middleware($auth);
        $router->map('POST', '/user/edit-profile', [BoneUserController::class, 'editProfileAction'])->middleware($auth);
        $router->map('GET', '/user/home', [BoneUserController::class, 'homePageAction'])->middleware($auth);
        $factory = new ResponseFactory();
        $strategy = new JsonStrategy($factory);
        $strategy->setContainer($c);

        $router->group('/api/user', function (RouteGroup $route) use ($auth) {
            $route->map('POST', '/choose-avatar', [BoneUserApiController::class, 'chooseAvatarAction'])->middleware($auth);
            $route->map('POST', '/upload-avatar', [BoneUserApiController::class, 'uploadAvatarAction'])->middleware($auth);
            $route->map('GET', '/avatar', [BoneUserApiController::class, 'avatar'])->middleware($auth);
        })
        ->setStrategy($strategy);

        $router->apiResource('people', PersonApiController::class, $c);
        $router->adminResource('people', PersonAdminController::class, $c);

        return $router;
    }

    public function registerConsoleCommands(Container $container): array
    {
        return [
            new ResetPasswordCommand($container->get(UserService::class)),
            new CreateUserCommand($container->get(UserService::class)),
        ];
    }

    public function getFixtures(): array
    {
        return [
            LoadUsers::class
        ];
    }
}
