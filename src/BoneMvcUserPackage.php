<?php

declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser;

use Barnacle\Container;
use Barnacle\RegistrationInterface;
use BoneMvc\Module\BoneMvcUser\Controller\BoneMvcUserApiController;
use BoneMvc\Module\BoneMvcUser\Controller\BoneMvcUserController;
use Bone\Mvc\Router\RouterConfigInterface;
use Bone\Mvc\View\PlatesEngine;
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

        $c[BoneMvcUserController::class] = $c->factory(function (Container $c) {
            /** @var PlatesEngine $viewEngine */
            $viewEngine = $c->get(PlatesEngine::class);

            return new BoneMvcUserController($viewEngine);
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
        $router->map('GET', '/bonemvcuser', [BoneMvcUserController::class, 'indexAction']);

        $factory = new ResponseFactory();
        $strategy = new JsonStrategy($factory);
        $strategy->setContainer($c);

        $router->group('/api', function (RouteGroup $route) {
            $route->map('GET', '/bonemvcuser', [BoneMvcUserApiController::class, 'indexAction']);
        })
        ->setStrategy($strategy);

        return $router;
    }
}
