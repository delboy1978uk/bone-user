<?php

namespace BoneTest\User\Controller;

use Barnacle\Container;
use Bone\Controller\Init;
use Bone\Mail\Service\MailService;
use Bone\Paseto\PasetoService;
use Bone\Server\SiteConfig;
use Bone\User\Controller\BoneUserController;
use Bone\View\ViewEngine;
use Codeception\TestCase\Test;
use Del\Service\UserService;
use Del\SessionManager;
use Laminas\Diactoros\ServerRequest;
use Laminas\I18n\Translator\Translator;
use Psr\Http\Message\ResponseInterface;

class BoneUserControllerTest extends Test
{
    /** @var BoneUserController $controller */
    private $controller;

    public function _before()
    {
        $container = new Container();
        $container[Translator::class] = $this->createMock(Translator::class);
        $container[ViewEngine::class] = $this->createMock(ViewEngine::class);
        $container[SiteConfig::class] = $config = $this->createMock(SiteConfig::class);
        $container[SessionManager::class] = SessionManager::getInstance();
        $config->method('getLogo')->willReturn('/img/logo.png');

        $userService = $this->createMock(UserService::class);
        $mailService = $this->createMock(MailService::class);
        $pasetoService = $this->createMock(PasetoService::class);
        $controller = new BoneUserController($userService, $mailService, '/user/home',
            'layouts::admin', $pasetoService, true, true, true);
        $controller = Init::controller($controller, $container);
        $this->controller = $controller;
    }

    public function _after()
    {
        unset($this->controller);
    }

    public function testIndex()
    {
        $request = new ServerRequest();
        $response = $this->controller->indexAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
