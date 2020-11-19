<?php

declare(strict_types=1);

namespace Bone\User;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\Http\Middleware\HalEntity;
use Bone\Http\Middleware\JsonParse;
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
        /** @var ViewEngine $viewEngine */
        $viewEngine = $c->get(ViewEngine::class);

        if (!$c->has(UserService::class)) {
            $package = new UserPackage();
            $package->addToContainer($c);
        }

        $loginWidget = new LoginWidget($c->get(UserService::class), $c->get(Translator::class));
        $viewEngine->loadExtension($loginWidget);

        $c[BoneUserController::class] = $c->factory(function (Container $c) {
            /** @var MailService $mailService */
            $mailService = $c->get(MailService::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);
            $loginRedirectRoute = '/user/home';

            if ($c->has('bone-user')) {
                $options = $c->get('bone-user');
                $loginRedirectRoute = $options['loginRedirectRoute'] ?? null;
            }

            return  Init::controller(new BoneUserController($userService, $mailService, $loginRedirectRoute), $c);
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
     * @param Router $router
     * @return Router
     */
    public function addRoutes(Container $c, Router $router): Router
    {
        $router->map('GET', '/user', [BoneUserController::class, 'indexAction']);
        $router->map('GET', '/user/activate/{email}/{token}', [BoneUserController::class, 'activateAction']);
        $router->map('GET', '/user/change-password', [BoneUserController::class, 'changePasswordAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/change-password', [BoneUserController::class, 'changePasswordAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/change-email', [BoneUserController::class, 'changeEmailAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/change-email', [BoneUserController::class, 'changeEmailAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/edit-profile', [BoneUserController::class, 'editProfileAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/edit-profile', [BoneUserController::class, 'editProfileAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/lost-password/{email}', [BoneUserController::class, 'forgotPasswordAction']);
        $router->map('GET', '/user/home', [BoneUserController::class, 'homePageAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/login', [BoneUserController::class, 'loginAction']);
        $router->map('POST', '/user/login', [BoneUserController::class, 'loginFormAction']);
        $router->map('GET', '/user/logout', [BoneUserController::class, 'logoutAction']);
        $router->map('GET', '/user/register', [BoneUserController::class, 'registerAction']);
        $router->map('POST', '/user/register', [BoneUserController::class, 'registerAction']);
        $router->map('GET', '/user/reset-email/{email}/{new-email}/{token}', [BoneUserController::class, 'resetEmailAction']);
        $router->map('GET', '/user/reset-password/{email}/{token}', [BoneUserController::class, 'resetPasswordAction']);
        $router->map('POST', '/user/reset-password/{email}/{token}', [BoneUserController::class, 'resetPasswordAction']);
        $router->map('GET', '/user/resend-activation-mail/{email}', [BoneUserController::class, 'resendActivationEmailAction']);

        $factory = new ResponseFactory();
        $strategy = new JsonStrategy($factory);
        $strategy->setContainer($c);

        $router->group('/api', function (RouteGroup $route) use ($c) {
            $route->map('POST', '/user/choose-avatar', [BoneUserApiController::class, 'chooseAvatarAction'])->middleware($c->get(SessionAuth::class));
            $route->map('POST', '/user/upload-avatar', [BoneUserApiController::class, 'uploadAvatarAction'])->middleware($c->get(SessionAuth::class));
        })
        ->setStrategy($strategy);

        return $router;
    }
}
