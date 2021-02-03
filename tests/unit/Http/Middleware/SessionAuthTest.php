<?php

namespace BoneTest\User\Http\Middleware;

use Bone\Http\Response;
use Bone\Paseto\PasetoService;
use Bone\User\Http\Middleware\SessionAuth;
use Codeception\TestCase\Test;
use Del\Entity\User;
use Del\Exception\UserException;
use Del\Person\Entity\Person;
use Del\Person\Service\PersonService;
use Del\Service\UserService;
use Del\SessionManager;
use Exception;
use Laminas\Diactoros\ServerRequest;
use ParagonIE\Paseto\JsonToken;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionAuthTest extends Test
{
    /** @var SessionAuth $middleware */
    private $middleware;

    /** @var MockObject $userService */
    private $userService;

    /** @var MockObject $pasetoService */
    private $pasetoService;

    public function _before()
    {
        $user = new User();
        $person = new Person();
        $user->setPerson($person);
        $token = $this->createMock(JsonToken::class);
        $this->pasetoService = $this->createMock(PasetoService::class);
        $personService = $this->createMock(PersonService::class);
        $this->userService = $this->createMock(UserService::class);
        $this->middleware = new SessionAuth(SessionManager::getInstance(), $this->userService, $this->pasetoService);
        $this->userService->method('getPersonSvc')->willReturn($personService);
        $this->userService->method('findUserById')->willReturn($user);
        $personService->method('toArray')->willReturn([]);
        $this->pasetoService->method('decryptToken')->willReturn($token);
        $token->method('getClaims')->willReturn(['user' => 1]);
    }

    public function _after()
    {
        $session = SessionManager::getInstance();
        $session->has('user') ? $session->unset('user') : null;
    }

    public function testProcess401()
    {
        $this->expectException(UserException::class);
        $request = new ServerRequest();
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
        $response = $this->middleware->process($request, $handler);
    }

    public function testProcess()
    {
        SessionManager::getInstance()->set('user', 1);
        $request = new ServerRequest();
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
        $response = $this->middleware->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testProcessByRememberMeCookie()
    {
        $request = new ServerRequest();
        $request = $request->withCookieParams(['resu' => 1]);
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
        $response = $this->middleware->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testTokenException()
    {
        $request = new ServerRequest();
        $request = $request->withCookieParams(['resu' => 1]);
        $this->pasetoService->method('decryptToken')->willThrowException(new Exception());
        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
        $response = $this->middleware->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}