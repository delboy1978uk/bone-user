<?php

declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use Bone\I18n\I18nRegistrationInterface;
use Bone\Mvc\Controller\Init;
use BoneMvc\Mail\Service\MailService;
use BoneMvc\Module\BoneMvcUser\Controller\BoneMvcUserApiController;
use BoneMvc\Module\BoneMvcUser\Controller\BoneMvcUserController;
use Bone\Mvc\Router\RouterConfigInterface;
use Bone\Mvc\View\PlatesEngine;
use BoneMvc\Module\BoneMvcUser\Http\Middleware\SessionAuth;
use BoneMvc\Module\BoneMvcUser\View\Helper\LoginWidget;
use Del\Service\UserService;
use Del\SessionManager;
use Del\UserPackage;
use League\Route\RouteGroup;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Zend\Diactoros\ResponseFactory;
use Zend\I18n\Translator\Translator;

class BoneMvcUserPackage implements RegistrationInterface, RouterConfigInterface, I18nRegistrationInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c)
    {
        /** @var PlatesEngine $viewEngine */
        $viewEngine = $c->get(PlatesEngine::class);
        $viewEngine->addFolder('bonemvcuser', __DIR__ . '/View/BoneMvcUser/');
        $viewEngine->addFolder('email.user', __DIR__ . '/View/email/');

        
        $loginWidget = new LoginWidget($c->get(UserService::class), $c->get(Translator::class));
        $viewEngine->loadExtension($loginWidget);

        if (!$c->has(UserService::class)) {
            $package = new UserPackage();
            $package->addToContainer($c);
        }

        $c[BoneMvcUserController::class] = $c->factory(function (Container $c) {
            /** @var MailService $mailService */
            $mailService = $c->get(MailService::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);

            return  Init::controller(new BoneMvcUserController($userService, $mailService), $c);
        });

        $c[BoneMvcUserApiController::class] = $c->factory(function (Container $c) {
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);
            $dir = $c->get('uploads_dir');
            $img = $c->get('image_dir');
            $tmp = $c->get('temp_dir');

            return new BoneMvcUserApiController($userService, $dir, $img, $tmp);
        });


        $c[SessionAuth::class] = $c->factory(function (Container $c) {
            /** @var SessionManager $session */
            $session = $c->get(SessionManager::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);

            return new SessionAuth($session, $userService);
        });
    }

    /**
     * @return string
     */
    public function getEntityPath(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function hasEntityPath(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTranslationsDirectory(): string
    {
        return dirname(__DIR__) . '/data/translations';
    }


    /**
     * @param Container $c
     * @param Router $router
     * @return Router
     */
    public function addRoutes(Container $c, Router $router): Router
    {
        $router->map('GET', '/user', [BoneMvcUserController::class, 'indexAction']);
        $router->map('GET', '/user/activate/{email}/{token}', [BoneMvcUserController::class, 'activateAction']);
        $router->map('GET', '/user/change-password', [BoneMvcUserController::class, 'changePasswordAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/change-password', [BoneMvcUserController::class, 'changePasswordAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/change-email', [BoneMvcUserController::class, 'changeEmailAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/change-email', [BoneMvcUserController::class, 'changeEmailAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/edit-profile', [BoneMvcUserController::class, 'editProfileAction'])->middleware($c->get(SessionAuth::class));
        $router->map('POST', '/user/edit-profile', [BoneMvcUserController::class, 'editProfileAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/lost-password/{email}', [BoneMvcUserController::class, 'forgotPasswordAction']);
        $router->map('GET', '/user/home', [BoneMvcUserController::class, 'homePageAction'])->middleware($c->get(SessionAuth::class));
        $router->map('GET', '/user/login', [BoneMvcUserController::class, 'loginAction']);
        $router->map('POST', '/user/login', [BoneMvcUserController::class, 'loginFormAction']);
        $router->map('GET', '/user/logout', [BoneMvcUserController::class, 'logoutAction']);
        $router->map('GET', '/user/register', [BoneMvcUserController::class, 'registerAction']);
        $router->map('POST', '/user/register', [BoneMvcUserController::class, 'registerAction']);
        $router->map('GET', '/user/reset-email/{email}/{new-email}/{token}', [BoneMvcUserController::class, 'resetEmailAction']);
        $router->map('GET', '/user/reset-password/{email}/{token}', [BoneMvcUserController::class, 'resetPasswordAction']);
        $router->map('POST', '/user/reset-password/{email}/{token}', [BoneMvcUserController::class, 'resetPasswordAction']);
        $router->map('GET', '/user/resend-activation-mail/{email}', [BoneMvcUserController::class, 'resendActivationEmailAction']);

        $factory = new ResponseFactory();
        $strategy = new JsonStrategy($factory);
        $strategy->setContainer($c);

        $router->group('/api', function (RouteGroup $route) use ($c) {
            $route->map('GET', '/user', [BoneMvcUserApiController::class, 'indexAction']);
            $route->map('POST', '/user/choose-avatar', [BoneMvcUserApiController::class, 'chooseAvatarAction'])->middleware($c->get(SessionAuth::class));
            $route->map('POST', '/user/upload-avatar', [BoneMvcUserApiController::class, 'uploadAvatarAction'])->middleware($c->get(SessionAuth::class));
        })
        ->setStrategy($strategy);

        return $router;
    }
}
