<?php declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser\Controller;

use Bone\Mvc\View\ViewEngine;
use BoneMvc\Mail\Service\MailService;
use BoneMvc\Module\BoneMvcUser\Form\RegistrationForm;
use Del\Exception\UserException;
use Del\Service\UserService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class BoneMvcUserController
{
    /** @var ViewEngine $view */
    private $view;

    /** @var UserService $userService */
    private $userService;

    /** @var MailService $mailService */
    private $mailService;

    public function __construct(ViewEngine $view, UserService $userService, MailService $mailService)
    {
        $this->view = $view;
        $this->userService = $userService;
        $this->mailService = $mailService;
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

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     * @throws UserException
     */
    public function registerAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $form = new RegistrationForm('register');

        if ($request->getMethod() == 'POST') {

            $formData = $request->getParsedBody();
            $form->populate($formData);

            if ($form->isValid()) {
                $data = $form->getValues();
                try {
                    $user = $this->userService->registerUser($data);
                    $link = $this->userService->generateEmailLink($user);
                    $mail = $this->mailService;
                    $env = $this->getServerEnvironment();
                    $email = $user->getEmail();
                    $token = $link->getToken();

                    $message = $this->getViewEngine()->render('emails/user_registration/user_registration', [
                        'siteUrl' => $env->getSiteURL(),
                        'activationLink' => '/' . $this->getParam('locale') . '/activate-user-account/' . $email . '/' . $token,
                    ]);

                    $mail->setFrom('noreply@' . $env->getServerName())
                        ->setTo($user->getEmail())
                        ->setSubject($this->getTranslator()
                                ->translate('email.user.register.thankswith') . ' ' . Registry::ahoy()->get('site')['name'])
                        ->setMessage($message)
                        ->send();
                    $this->sendJsonObjectResponse($link);

                } catch (UserException $e) {

                    switch ($e->getMessage()) {
                        case UserException::USER_EXISTS:
                        case UserException::WRONG_PASSWORD:
                            throw new Exception($e->getMessage(), 400);
                            break;
                    }
                    throw $e;
                }
            }

        }

        $body = $this->view->render('bonemvcuser::register', ['form' => $form]);

        return new HtmlResponse($body);
    }
}
