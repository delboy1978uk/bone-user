<?php

namespace Bone\Test\User\Controller;

use Barnacle\Container;
use Bone\Controller\Init;
use Bone\Mail\Service\MailService;
use Bone\Paseto\PasetoService;
use Bone\Server\SiteConfig;
use Bone\User\Controller\BoneUserApiController;
use Bone\User\Controller\BoneUserController;
use Bone\View\ViewEngine;
use Bone\View\ViewEngineInterface;
use Codeception\Test\Unit;
use Del\Entity\EmailLink;
use Del\Entity\User;
use Del\Exception\EmailLinkException;
use Del\Exception\UserException;
use Del\Image;
use Del\Person\Entity\Person;
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

class ApiControllerTest extends Unit
{
    /** @var BoneUserApiController $controller */
    private $controller;

    /** @var MockObject $userServiceMock */
    private $userServiceMock;

    public function _before()
    {
        $container = new Container();
        $container[Translator::class] = $translator = $this->createMock(Translator::class);
        $container[ViewEngineInterface::class] = $view = $this->createMock(ViewEngine::class);
        $container[SiteConfig::class] = $config = $this->createMock(SiteConfig::class);
        $container[SessionManager::class] = SessionManager::getInstance();
        $config->method('getLogo')->willReturn('/img/logo.png');
        $config->method('getTitle')->willReturn('Test Site - Danger');
        $config->method('getAttribute')->willReturn([
            'date_format' => 'd/m/Y',
        ]);
        $view->method('render')->willReturn('content!');
        $translator->method('translate')->willReturn('lorem ipsum');

        $user = $this->createMock(User::class);
        $userService = $this->userServiceMock = $this->createMock(UserService::class);
        $mailService = $this->createMock(MailService::class);
        $user->method('getEmail')->willReturn('man@work.com');
        $userService->method('registerUser')->willReturn($user);
        $mailService->method('getSiteConfig')->willReturn($config);

        $controller = new BoneUserApiController($userService, 'tests/_data/', 'img/', 'tests/_data/img/', $mailService);
        $controller = Init::controller($controller, $container);
        $this->controller = $controller;
    }

    public function _after()
    {
        unset($this->controller);
        foreach (glob('tests/_data/img/*.png') as $image) {
            unlink ($image);
        }
    }

    public function testChooseAvatar()
    {
        mkdir('public');
        copy('data/assets/img/avatars/gorilla.png', 'public/gorilla.png');
        $user = new User();
        $person = new Person();
        $user->setPerson($person);
        $request = new ServerRequest();
        $request = $request->withAttribute('user', $user);
        $request = $request->withMethod('POST');
        $request = $request->withParsedBody([
            'avatar' => '/gorilla.png',
        ]);
        $response = $this->controller->chooseAvatarAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        unlink('public/gorilla.png');
        rmdir('public');
    }

    public function testUploadAvatar()
    {
        copy('data/assets/img/avatars/gorilla.png', 'tests/_data/img/gorilla.png');
        $user = new User();
        $person = new Person();
        $user->setPerson($person);
        $_FILES = [
            'avatar' => [
                'name' => 'gorilla.png',
                'type' => 'image/png',
                'tmp_name' => 'gorilla.png',
                'error' => 0,
                'size' => 12345,
            ],
        ];
        $request = new ServerRequest();
        $request = $request->withParsedBody(['image' => 'whatever.png']);
        $request = $request->withAttribute('user', $user);
        $response = $this->controller->uploadAvatarAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testUploadAvatarPortrait()
    {
        $image = new Image('data/assets/img/avatars/gorilla.png');
        $image->crop(round($image->getWidth() / 2), $image->getHeight());
        $image->save('tests/_data/img/gorilla.png');
        $user = new User();
        $person = new Person();
        $user->setPerson($person);
        $_FILES = [
            'avatar' => [
                'name' => 'gorilla.png',
                'type' => 'image/png',
                'tmp_name' => 'gorilla.png',
                'error' => 0,
                'size' => 12345,
            ],
        ];
        $request = new ServerRequest();
        $request = $request->withParsedBody(['image' => 'whatever.png']);
        $request = $request->withAttribute('user', $user);
        $response = $this->controller->uploadAvatarAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testUploadAvatarLandscape()
    {
        $image = new Image('data/assets/img/avatars/gorilla.png');
        $image->crop($image->getWidth(), round($image->getHeight() / 2));
        $image->save('tests/_data/img/gorilla.png');
        $user = new User();
        $person = new Person();
        $user->setPerson($person);
        $_FILES = [
            'avatar' => [
                'name' => 'gorilla.png',
                'type' => 'image/png',
                'tmp_name' => 'gorilla.png',
                'error' => 0,
                'size' => 12345,
            ],
        ];
        $request = new ServerRequest();
        $request = $request->withParsedBody(['image' => 'whatever.png']);
        $request = $request->withAttribute('user', $user);
        $response = $this->controller->uploadAvatarAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testUploadAvatarInvalidForm()
    {
        copy('data/assets/img/avatars/gorilla.png', 'tests/_data/img/gorilla.png');
        $user = new User();
        $person = new Person();
        $user->setPerson($person);
        $_FILES = ['avatar' => []];
        $request = new ServerRequest();
        $request = $request->withParsedBody(['image' => 'whatever.png']);
        $request = $request->withAttribute('user', $user);
        $response = $this->controller->uploadAvatarAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testUploadAvatarException()
    {
        copy('data/assets/img/avatars/gorilla.png', 'tests/_data/img/gorilla.png');
        $user = $this->createMock(User::class);
        $person = $this->createMock(Person::class);
        $person->method('setImage')->willThrowException(new Exception('what'));
        $user->method('getPerson')->willReturn($person);
        $_FILES = [
            'avatar' => [
                'name' => 'gorilla.png',
                'type' => 'image/png',
                'tmp_name' => 'gorilla.png',
                'error' => 0,
                'size' => 12345,
            ],
        ];
        $request = new ServerRequest();
        $request = $request->withParsedBody(['image' => 'whatever.png']);
        $request = $request->withAttribute('user', $user);
        $response = $this->controller->uploadAvatarAction($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }



    public function testAvatar()
    {
        copy('data/assets/img/avatars/gorilla.png', 'tests/_data/img/gorilla.png');
        $user = new User();
        $person = new Person();
        $person->setImage('img/gorilla.png');
        $user->setPerson($person);
        $request = new ServerRequest();
        $request = $request->withAttribute('user', $user);
        $response = $this->controller->avatar($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
