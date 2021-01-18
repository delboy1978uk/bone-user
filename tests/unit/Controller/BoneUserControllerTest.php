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
use Del\Exception\EmailLinkException;
use Del\Exception\UserException;
use Del\Service\UserService;
use Del\SessionManager;
use Del\Value\User\State;
use Exception;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequest;
use Laminas\I18n\Translator\Translator;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;

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
            'layouts::admin', $pasetoService, true, true, false);
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

    public function testActivate()
    {
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $request = $request->withAttribute('token', 'asdfghjkl');
        $response = $this->controller->activateAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testActivateWithExpiredLink()
    {
        $this->userServiceMock->method('findEmailLink')->willThrowException(new EmailLinkException(EmailLinkException::LINK_EXPIRED));
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $request = $request->withAttribute('token', 'asdfghjkl');
        $response = $this->controller->activateAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testActivateWithNoLinkFound()
    {
        $this->userServiceMock->method('findEmailLink')->willThrowException(new EmailLinkException(EmailLinkException::LINK_NOT_FOUND));
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $request = $request->withAttribute('token', 'asdfghjkl');
        $response = $this->controller->activateAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLogin()
    {
        $request = new ServerRequest();
        $response = $this->controller->loginAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLoginForm()
    {
        $request = new ServerRequest();
        $response = $this->controller->loginFormAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLoginFormWithValidData()
    {
        $this->userServiceMock->method('findUserById')->willReturn(new User());
        $this->controller->getSession()->set('loginRedirectRoute', '/admin');
        $request = new ServerRequest();
        $request = $request->withParsedBody([
            'email' => 'man@work.com',
            'password' => 'xxxxxx',
        ]);
        $response = $this->controller->loginFormAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }


    public function testLoginFormWrongPassword()
    {
        $this->userServiceMock->method('findUserById')->willThrowException(new UserException(UserException::WRONG_PASSWORD));
        $this->controller->getSession()->set('loginRedirectRoute', '/admin');
        $request = new ServerRequest();
        $request = $request->withParsedBody([
            'email' => 'man@work.com',
            'password' => 'xxxxxx',
        ]);
        $response = $this->controller->loginFormAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }


    public function testLoginFormUserUnactivated()
    {
        $this->userServiceMock->method('findUserById')->willThrowException(new UserException(UserException::USER_UNACTIVATED));
        $this->controller->getSession()->set('loginRedirectRoute', '/admin');
        $request = new ServerRequest();
        $request = $request->withParsedBody([
            'email' => 'man@work.com',
            'password' => 'xxxxxx',
        ]);
        $response = $this->controller->loginFormAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }


    public function testLoginFormUserBanned()
    {
        $this->userServiceMock->method('findUserById')->willThrowException(new UserException(UserException::USER_BANNED));
        $this->controller->getSession()->set('loginRedirectRoute', '/admin');
        $request = new ServerRequest();
        $request = $request->withParsedBody([
            'email' => 'man@work.com',
            'password' => 'xxxxxx',
        ]);
        $response = $this->controller->loginFormAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }


    public function testLoginFormUserException()
    {
        $this->userServiceMock->method('findUserById')->willThrowException(new UserException('something else'));
        $this->controller->getSession()->set('loginRedirectRoute', '/admin');
        $request = new ServerRequest();
        $request = $request->withParsedBody([
            'email' => 'man@work.com',
            'password' => 'xxxxxx',
        ]);
        $response = $this->controller->loginFormAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHomePage()
    {
        $request = new ServerRequest();
        $response = $this->controller->homePageAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHomePageWithRedirect()
    {
        $mirror = new ReflectionClass(BoneUserController::class);
        $property = $mirror->getProperty('loginRedirectRoute');
        $property->setAccessible(true);
        $property->setValue($this->controller, '/admin');
        $request = new ServerRequest();
        $response = $this->controller->homePageAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLogout()
    {
        $request = new ServerRequest();
        $response = $this->controller->logoutAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testResendActivationEmailThrowsException()
    {
        $this->expectException(Exception::class);
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $response = $this->controller->resendActivationEmailAction($request);
    }

    public function testResendActivationEmail()
    {
        $user = new User();
        $user->setEmail('man@work.com');
        $this->userServiceMock->method('findUserByEmail')->willReturn($user);
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $response = $this->controller->resendActivationEmailAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testResendActivationEmailUserActivatedAlready()
    {
        $user = new User();
        $user->setEmail('man@work.com');
        $user->setState(new State(State::STATE_ACTIVATED));
        $this->userServiceMock->method('findUserByEmail')->willReturn($user);
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $response = $this->controller->resendActivationEmailAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testResendActivationEmailUHandleException()
    {
        $user = new User();
        $user->setEmail('man@work.com');
        $this->userServiceMock->method('findUserByEmail')->willReturn($user);
        $this->userServiceMock->method('generateEmailLink')->willThrowException(new Exception('argh'));
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $response = $this->controller->resendActivationEmailAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testForgotPasswordUserNotFound()
    {
        $this->expectException(Exception::class);
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $response = $this->controller->forgotPasswordAction($request);
    }

    public function testForgotPasswordUnactivatedUser()
    {
        $user = new User();
        $user->setEmail('man@work.com');
        $this->userServiceMock->method('findUserByEmail')->willReturn($user);
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $response = $this->controller->forgotPasswordAction($request);
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testForgotPassword()
    {
        $user = new User();
        $user->setEmail('man@work.com');
        $user->setState(new State(State::STATE_ACTIVATED));
        $this->userServiceMock->method('findUserByEmail')->willReturn($user);
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $response = $this->controller->forgotPasswordAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testForgotPasswordHandleException()
    {
        $this->userServiceMock->method('generateEmailLink')->willThrowException(new Exception('oops'));
        $user = new User();
        $user->setEmail('man@work.com');
        $user->setState(new State(State::STATE_ACTIVATED));
        $this->userServiceMock->method('findUserByEmail')->willReturn($user);
        $request = new ServerRequest();
        $request = $request->withAttribute('email', 'man@work.com');
        $response = $this->controller->forgotPasswordAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
