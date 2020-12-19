<?php

declare(strict_types=1);

namespace Bone\User;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Http\Middleware\HalEntity;
use Bone\Http\Middleware\JsonParse;
use Bone\Http\Middleware\Stack;
use Bone\I18n\I18nRegistrationInterface;
use Bone\Controller\Init;
use Bone\Mail\Service\MailService;
use Bone\OAuth2\Http\Middleware\ResourceServerMiddleware;
use Bone\OAuth2\Http\Middleware\ScopeCheck;
use Bone\Server\SiteConfig;
use Bone\User\Controller\BoneUserApiController;
use Bone\User\Controller\BoneUserController;
use Bone\Router\Router;
use Bone\Router\RouterConfigInterface;
use Bone\View\ViewEngine;
use Bone\User\Http\Middleware\SessionAuth;
use Bone\User\Http\Middleware\SessionAuthRedirect;
use Bone\User\View\Helper\LoginWidget;
use Bone\View\ViewRegistrationInterface;
use Del\Booty\AssetRegistrationInterface;
use Del\Service\UserService;
use Del\SessionManager;
use Del\UserPackage;
use League\Route\RouteGroup;
use League\Route\Strategy\JsonStrategy;
use Laminas\Diactoros\ResponseFactory;
use Laminas\I18n\Translator\Translator;

class BoneUserPackage implements RegistrationInterface, RouterConfigInterface, I18nRegistrationInterface, AssetRegistrationInterface, ViewRegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c)
    {
        if (!$c->has(UserService::class)) {
            $package = new UserPackage();
            $package->addToContainer($c);
        }

        $c[BoneUserController::class] = $c->factory(function (Container $c) {
            /** @var MailService $mailService */
            $mailService = $c->get(MailService::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);
            $loginRedirectRoute = '/user/home';
            $defaultLayout = $c->get('default_layout');
            $adminLayout = $c->has('admin_layout') ? $c->get('admin_layout') : $defaultLayout;

            if ($c->has('bone-user')) {
                $options = $c->get('bone-user');
                $loginRedirectRoute = $options['loginRedirectRoute'] ?? '/user/home';
                $registrationEnabled = $options['enableRegistration'] ?: true;
                $profileRequired = $options['requireProfile'] ?: false;
            }

            return  Init::controller(new BoneUserController($userService, $mailService, $loginRedirectRoute, $adminLayout, $registrationEnabled, $profileRequired), $c);
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

            return new SessionAuth($session, $userService);
        });

        $c[SessionAuthRedirect::class] = $c->factory(function (Container $c) {
            /** @var SessionManager $session */
            $session = $c->get(SessionManager::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);

            return new SessionAuthRedirect($session, $userService);
        });
    }

    /**
     * @return array
     */
    public function getAssetFolders(): array
    {
        return [
            'bone-user' => dirname(__DIR__) . '/data/assets',
        ];
    }


    /**
     * @return string
     */
    public function getTranslationsDirectory(): string
    {
        return dirname(__DIR__) . '/data/translations';
    }

    /**
     * @return array
     */
    public function addViews(): array
    {
        return [
            'boneuser' => __DIR__ . '/View/BoneUser/',
            'email.user' => __DIR__ . '/View/email/',
        ];
    }

    /**
     * @param Container $c
     * @return array
     */
    public function addViewExtensions(Container $c): array
    {
        $userService = $c->get(UserService::class);
        $mailService = $c->get(Translator::class);
        $loginWidget = new LoginWidget($userService, $mailService);

        return [$loginWidget];
    }

    /**
     * @param Stack $stack
     * @param Container $container
     */
    public function addMiddleware(Stack $stack, Container $container): void
    {
        // TODO: Implement addMiddleware() method.
    }


    /**
     * @param Container $c
     * @param Router $router
     * @return Router
     */
    public function addRoutes(Container $c, Router $router): Router
    {
        $router->group('/user', function (RouteGroup $route) {
            $route->map('GET', '/', [BoneUserController::class, 'indexAction']);
            $route->map('GET', '/lost-password/{email}', [BoneUserController::class, 'forgotPasswordAction']);
            $route->map('GET', '/login', [BoneUserController::class, 'loginAction']);
            $route->map('POST', '/login', [BoneUserController::class, 'loginFormAction']);
            $route->map('GET', '/logout', [BoneUserController::class, 'logoutAction']);
            $route->map('GET', '/register', [BoneUserController::class, 'registerAction']);
            $route->map('POST', '/register', [BoneUserController::class, 'registerAction']);
            $route->map('GET', '/activate/{email}/{token}', [BoneUserController::class, 'activateAction']);
            $route->map('GET', '/reset-email/{email}/{new-email}/{token}', [BoneUserController::class, 'resetEmailAction']);
            $route->map('GET', '/reset-password/{email}/{token}', [BoneUserController::class, 'resetPasswordAction']);
            $route->map('POST', '/reset-password/{email}/{token}', [BoneUserController::class, 'resetPasswordAction']);
            $route->map('GET', '/resend-activation-mail/{email}', [BoneUserController::class, 'resendActivationEmailAction']);
        });

        $auth = $c->get(SessionAuth::class);
        $router->map('GET', '/user/change-password', [BoneUserController::class, 'changePasswordAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/change-password', [BoneUserController::class, 'changePasswordAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/change-email', [BoneUserController::class, 'changeEmailAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/change-email', [BoneUserController::class, 'changeEmailAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/edit-profile', [BoneUserController::class, 'editProfileAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/edit-profile', [BoneUserController::class, 'editProfileAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/home', [BoneUserController::class, 'homePageAction'])->middleware($c->get(SessionAuth::class));


        $factory = new ResponseFactory();
        $strategy = new JsonStrategy($factory);
        $strategy->setContainer($c);

        $router->group('/api/user', function (RouteGroup $route) use ($c) {
            $route->map('POST', '/choose-avatar', [BoneUserApiController::class, 'chooseAvatarAction'])->middleware($c->get(SessionAuth::class));
            $route->map('POST', '/upload-avatar', [BoneUserApiController::class, 'uploadAvatarAction'])->middleware($c->get(SessionAuth::class));
            $route->map('GET', '/avatar', [BoneUserApiController::class, 'avatar'])->middleware($c->get(SessionAuth::class));
        })
        ->setStrategy($strategy);

        return $router;
    }
}
