<?php

declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use BoneMvc\Mail\Service\MailService;
use BoneMvc\Module\BoneMvcUser\Controller\BoneMvcUserApiController;
use BoneMvc\Module\BoneMvcUser\Controller\BoneMvcUserController;
use Bone\Mvc\Router\RouterConfigInterface;
use Bone\Mvc\View\PlatesEngine;
use Del\Service\UserService;
use Del\UserPackage;
use League\Route\RouteGroup;
use League\Route\Router;
use League\Route\Strategy\JsonStrategy;
use Zend\Diactoros\ResponseFactory;

class BoneMvcUserPackage implements RegistrationInterface, RouterConfigInterface
{
    /**
     * @param Container $c
     */
    public function addToContainer(Container $c)
    {
        /** @var PlatesEngine $viewEngine */
        $viewEngine = $c->get(PlatesEngine::class);
        $viewEngine->addFolder('bonemvcuser', __DIR__ . '/View/BoneMvcUser/');
        
        if (!$c->has(UserService::class)) {
            $package = new UserPackage();
            $package->addToContainer($c);
        }

        $c[BoneMvcUserController::class] = $c->factory(function (Container $c) {
            /** @var PlatesEngine $viewEngine */
            $viewEngine = $c->get(PlatesEngine::class);
            /** @var MailService $viewEngine */
            $mailService = $c->get(MailService::class);
            /** @var UserService $userService */
            $userService = $c->get(UserService::class);

            return new BoneMvcUserController($viewEngine, $userService, $mailService);
        });

        $c[BoneMvcUserApiController::class] = $c->factory(function (Container $c) {
            return new BoneMvcUserApiController();
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
     * @param Container $c
     * @param Router $router
     * @return Router
     */
    public function addRoutes(Container $c, Router $router): Router
    {
        $router->map('GET', '/user', [BoneMvcUserController::class, 'indexAction']);
        $router->map('GET', '/user/register', [BoneMvcUserController::class, 'registerAction']);
        $router->map('POST', '/user/register', [BoneMvcUserController::class, 'registerAction']);

        $factory = new ResponseFactory();
        $strategy = new JsonStrategy($factory);
        $strategy->setContainer($c);

        $router->group('/api', function (RouteGroup $route) {
            $route->map('GET', '/user', [BoneMvcUserApiController::class, 'indexAction']);
        })
        ->setStrategy($strategy);

        return $router;
    }
}
