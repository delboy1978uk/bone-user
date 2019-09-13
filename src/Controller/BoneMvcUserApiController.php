<?php

declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class BoneMvcUserApiController
{
    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function indexAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        return new JsonResponse([
            'drink' => 'grog',
            'pieces' => 'of eight',
            'shiver' => 'me timbers',
        ]);
    }
}
