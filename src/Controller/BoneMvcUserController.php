<?php declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser\Controller;

use Bone\Mvc\Controller;
use Bone\Mvc\View\ViewEngine;
use BoneMvc\Mail\EmailMessage;
use BoneMvc\Mail\Service\MailService;
use BoneMvc\Module\BoneMvcUser\Form\RegistrationForm;
use Del\Exception\UserException;
use Del\Service\UserService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class BoneMvcUserController extends Controller
{
    /** @var ViewEngine $view */
    private $view;

    /** @var UserService $userService */
    private $userService;

    /** @var MailService $mailService */
    private $mailService;

    /**
     * BoneMvcUserController constructor.
     * @param ViewEngine $view
     * @param UserService $userService
     * @param MailService $mailService
     */
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
        $message = null;

        if ($request->getMethod() == 'POST') {

            $formData = $request->getParsedBody();
            $form->populate($formData);

            if ($form->isValid()) {
                $data = $form->getValues();
                try {
                    $user = $this->userService->registerUser($data);
                    $link = $this->userService->generateEmailLink($user);
                    $mail = $this->mailService;

                    $env = $mail->getSiteConfig()->getEnvironment();
                    $email = $user->getEmail();
                    $token = $link->getToken();

                    $mail = new EmailMessage();
                    $mail->setTo($user->getEmail());
                    $mail->setSubject($this->getTranslator()->translate('email.user.register.thankswith') . ' ' . $this->mailService->getSiteConfig()->getTitle());
                    $mail->setTemplate('email.user::user_registration/user_registration');
                    $mail->setViewData([
                        'siteUrl' => $env->getSiteURL(),
                        'activationLink' => '/en_GB/activate-user-account/' . $email . '/' . $token,
                    ]);
                    $this->mailService->sendEmail($mail);
                    $body = $this->view->render('bonemvcuser::thanks-for-registering');

                    return new HtmlResponse($body);

                } catch (UserException $e) {
                    $message = [$e->getMessage(), 'danger'];
                }
            }
        }

        $body = $this->view->render('bonemvcuser::register', ['form' => $form, 'message' => $message]);

        return new HtmlResponse($body);
    }
}
