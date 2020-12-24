<?php

namespace Bone\User\Http\Middleware;

use Bone\Http\Response;
use Bone\Paseto\PasetoService;
use Bone\Server\SessionAwareInterface;
use Bone\Server\Traits\HasSessionTrait;
use Del\Exception\UserException;
use Del\Service\UserService;
use Del\SessionManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\Uri;

class SessionAuthRedirect implements MiddlewareInterface, SessionAwareInterface
{
    use HasSessionTrait;

    /** @var UserService $userService */
    private $userService;

    /** @var PasetoService $pasetoService */
    private $pasetoService;

    public function __construct(SessionManager $sessionManager, UserService $userService, PasetoService $pasetoService)
    {
        $this->setSession($sessionManager);
        $this->userService = $userService;
        $this->pasetoService = $pasetoService;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cookies = $request->getCookieParams();
        $id = $this->getSession()->get('user');

        if (!$id && isset($cookies['resu'])) {
            $string = $cookies['resu'];
            $token = $this->pasetoService->decryptToken($string);
            $id = $token->getClaims()['user'];
        }

        if ($id) {
            $user = $this->userService->findUserById($id);
            $request = $request->withAttribute('user', $user);

            $response = $handler->handle($request);

            if ($response instanceof Response) {
                $response->setAttribute('user', $user);
            }

            return $response;
        }

        $currentUrl = $request->getUri()->getPath() . '?' . $request->getUri()->getQuery();
        $this->getSession()->set('loginRedirectRoute', $currentUrl);

        return new RedirectResponse(new Uri('/user/login'));
    }
}