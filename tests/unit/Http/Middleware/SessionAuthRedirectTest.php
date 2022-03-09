<?php

namespace Bone\Test\User\Http\Middleware;

use Bone\Http\Response;
use Bone\Paseto\PasetoService;
use Bone\User\Http\Middleware\SessionAuthRedirect;
use Codeception\TestCase\Test;
use Del\Entity\User;
use Del\Exception\UserException;
use Del\Person\Entity\Person;
use Del\Person\Service\PersonService;
use Del\Service\UserService;
use Del\SessionManager;
use Laminas\Diactoros\ServerRequest;
use ParagonIE\Paseto\JsonToken;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionAuthRedirectTest extends Test
{
    /** @var SessionAuth $middleware */
    private $middleware;

    /** @var MockObject $userService */
    private $userService;

    public function _before()
    {
        $user = new User();
        $person = new Person();
        $user->setPerson($person);
        $token = $this->createMock(JsonToken::class);
        $pasetoService = $this->createMock(PasetoService::class);
        $personService = $this->createMock(PersonService::class);
        $this->userService = $this->createMock(UserService::class);
        $this->middleware = new SessionAuthRedirect(SessionManager::getInstance(), $this->userService, $pasetoService);
        $this->userService->method('getPersonSvc')->willReturn($personService);
        $this->userService->method('findUserById')->willReturn($user);
        $personService->method('toArray')->willReturn([]);
        $pasetoService->method('decryptToken')->willReturn($token);
        $token->method('getClaims')->willReturn(['user' => 1]);
    }

    public function _after()
    {
        $session = SessionManager::getInstance();
        $session->has('user') ? $session->unset('user') : null;
    }

    public function testProcessWithCookie()
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

    public function testProcessNotLoggedIn()
    {
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

}
