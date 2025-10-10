<?php

namespace Bone\Test\User;

use Barnacle\Container;
use Bone\Mail\Service\MailService;
use Bone\Paseto\PasetoService;
use Bone\Router\Router;
use Bone\Server\SiteConfig;
use Bone\User\BoneUserPackage;
use Bone\User\Controller\BoneUserApiController;
use Bone\User\Controller\BoneUserController;
use Bone\User\Http\Middleware\SessionAuth;
use Bone\User\Http\Middleware\SessionAuthRedirect;
use Bone\User\View\Helper\LoginWidget;
use Bone\View\ViewEngine;
use Codeception\Test\Unit;
use Del\Service\UserService;
use Del\SessionManager;
use Laminas\I18n\Translator\Translator;
use League\Route\RouteGroup;
use ReflectionClass;

class PackageTest extends Unit
{
    /** @var BoneUserPackage $package */
    private $package;

    /** @var Container $container */
    private $container;

    public function _before()
    {
        $this->container = new Container();
        $this->package = new BoneUserPackage();
        $this->container[MailService::class] = $this->createStub(MailService::class);
        $this->container[UserService::class] = $this->createStub(UserService::class);
        $this->container[PasetoService::class] = $this->createStub(PasetoService::class);
        $this->container[Translator::class] = $this->createStub(Translator::class);
        $this->container[ViewEngine::class] = $this->createStub(ViewEngine::class);
        $this->container[SiteConfig::class] = $this->createStub(SiteConfig::class);
        $this->container[SessionManager::class] = SessionManager::getInstance();
        $this->container['default_layout'] = 'layouts::bone';
        $this->container['admin_layout'] = 'layouts::admin';
        $this->container['uploads_dir'] = 'data/uploads/';
        $this->container['temp_dir'] = 'data/tmp/';
        $this->container['image_dir'] = 'img/';
        $this->container['bone-user'] = [
            'loginRedirectRoute' => '/admin',
            'enableRegistration' => true,
            'requireProfile' => false,
            'rememberMeCookie' => true,
        ];
        $this->package->addToContainer($this->container);
    }

    public function _after()
    {
        unset($this->container);
        unset($this->package);
    }

    public function testPackage()
    {
        $this->assertTrue($this->container->has(BoneUserController::class));
        $this->assertTrue($this->container->has(BoneUserApiController::class));
        $this->assertTrue($this->container->has(SessionAuth::class));
        $this->assertTrue($this->container->has(SessionAuthRedirect::class));
        $this->assertInstanceOf(BoneUserController::class, $this->container->get(BoneUserController::class));
        $this->assertInstanceOf(BoneUserApiController::class, $this->container->get(BoneUserApiController::class));
        $this->assertInstanceOf(SessionAuth::class, $this->container->get(SessionAuth::class));
        $this->assertInstanceOf(SessionAuthRedirect::class, $this->container->get(SessionAuthRedirect::class));
        $this->assertIsArray($this->package->registerConsoleCommands($this->container));
        $this->assertCount(2, $this->package->registerConsoleCommands($this->container));
    }

    public function testGetters()
    {
        $this->assertCount(1, $this->package->getAssetFolders());
        $this->assertArrayHasKey('bone-user', $this->package->getAssetFolders());

        $this->assertEquals(dirname(dirname(__DIR__)) . '/data/translations', $this->package->getTranslationsDirectory());

        $views = $this->package->addViews();
        $this->assertCount(2,$views );
        $this->assertArrayHasKey('boneuser', $views);
        $this->assertArrayHasKey('email.user', $views);

        $extensions = $this->package->addViewExtensions($this->container);
        $this->assertCount(1, $extensions);
        $this->assertInstanceOf(LoginWidget::class, $extensions[0]);

    }

    public function testAddRoutes()
    {
        $router = new Router();
        $this->package->addRoutes($this->container, $router);
        $mirror = new ReflectionClass(Router::class);
        $prop = $mirror->getProperty('groups');
        $prop->setAccessible(true);
        $groups = $prop->getValue($router);
        $this->assertCount(4, $groups);

        /** @var RouteGroup $group */
        foreach ($groups as $group) {
            $mirror = new ReflectionClass(RouteGroup::class);
            $prop = $mirror->getProperty('callback');
            $prop->setAccessible(true);
            $callable = $prop->getValue($group);
            $callable($group);
        }

        $routes = $router->getRoutes();
        $this->assertCount(35, $routes);
    }
}
