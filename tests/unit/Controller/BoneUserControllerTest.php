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
use Del\Entity\User;
use Del\Exception\UserException;
use Del\Service\UserService;
use Del\SessionManager;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\I18n\Translator\Translator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;

class BoneUserControllerTest extends Test
{
    /** @var BoneUserController $controller */
    private $controller;

    /** @var MockObject $userServiceMock */
    private $userServiceMock;

    public function _before()
    {
        $container = new Container();
        $container[Translator::class] = $translator = $this->createMock(Translator::class);
        $container[ViewEngine::class] = $view = $this->createMock(ViewEngine::class);
        $container[SiteConfig::class] = $config = $this->createMock(SiteConfig::class);
        $container[SessionManager::class] = SessionManager::getInstance();
        $config->method('getLogo')->willReturn('/img/logo.png');
        $config->method('getTitle')->willReturn('Test Site - Danger');
        $view->method('render')->willReturn('content!');
        $translator->method('translate')->willReturn('lorem ipsum');

        $user = $this->createMock(User::class);
        $userService = $this->userServiceMock = $this->createMock(UserService::class);
        $mailService = $this->createMock(MailService::class);
        $pasetoService = $this->createMock(PasetoService::class);
        $user->method('getEmail')->willReturn('man@work.com');
        $userService->method('registerUser')->willReturn($user);
        $mailService->method('getSiteConfig')->willReturn($config);

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
        $this->controller->getSession()->set('user', 1);
        $response = $this->controller->indexAction($request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->controller->getSession()->unset('user');
    }

    public function testRegister()
    {
        $request = new ServerRequest();
        $response = $this->controller->registerAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRegisterPostBadData()
    {
        $request = new ServerRequest([],[],'/user/register/', 'POST');
        $request = $request->withParsedBody(['data' => 'garbage']);
        $response = $this->controller->registerAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRegisterPostValidData()
    {
        $request = new ServerRequest([],[],'/user/register/', 'POST');
        $request = $request->withParsedBody([
            'email' => 'man@work.com',
            'password' => 'xxxxxx',
            'confirm' => 'xxxxxx',
        ]);
        $response = $this->controller->registerAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testRegisterPostValidDataUserException()
    {
        $this->userServiceMock->method('registerUser')->willThrowException(new UserException(UserException::PERSON_EXISTS));
        $request = new ServerRequest([],[],'/user/register/', 'POST');
        $request = $request->withParsedBody([
            'email' => 'man@work.com',
            'password' => 'xxxxxx',
            'confirm' => 'xxxxxx',
        ]);
        $response = $this->controller->registerAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
