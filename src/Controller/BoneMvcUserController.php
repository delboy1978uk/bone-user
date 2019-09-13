<?php declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser\Controller;

use Bone\Mvc\View\ViewEngine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class BoneMvcUserController
{
    /** @var ViewEngine $view */
    private $view;

    public function __construct(ViewEngine $view)
    {
        $this->view = $view;
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface $response
     * @throws \Exception
     */
    public function indexAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $body = $this->view->render('bonemvcuser::index', []);

        return new HtmlResponse($body);
    }
}
