<?php

namespace Bone\User\Http\Middleware;

use Bone\Server\SessionAwareInterface;
use Bone\Server\Traits\HasSessionTrait;
use Del\Exception\UserException;
use Del\Service\UserService;
use Del\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionAuth implements MiddlewareInterface, SessionAwareInterface
{
    use HasSessionTrait;

    /** @var UserService $userService */
    private $userService;

    public function __construct(SessionManager $sessionManager, UserService $userService)
    {
        $this->setSession($sessionManager);
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($id = $this->getSession()->get('user')) {
            $user = $this->userService->findUserById($id);
            $request = $request->withAttribute('user', $user);
            $response = $handler->handle($request);
            $person = $user->getPerson();
            $person = $this->userService->getPersonSvc()->toArray($person);
            $user = $this->userService->toArray($user);
            $user['person'] = $person;

            return $response->withHeader('user', json_encode($user));
        }

        throw new UserException(UserException::UNAUTHORISED, 401);
    }
}