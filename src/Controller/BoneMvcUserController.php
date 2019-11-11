<?php declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser\Controller;

use Bone\Form;
use Bone\Mvc\Controller;
use Bone\Mvc\View\ViewEngine;
use BoneMvc\Mail\EmailMessage;
use BoneMvc\Mail\Service\MailService;
use BoneMvc\Module\BoneMvcUser\Form\LoginForm;
use BoneMvc\Module\BoneMvcUser\Form\PersonForm;
use BoneMvc\Module\BoneMvcUser\Form\RegistrationForm;
use BoneMvc\Module\BoneMvcUser\Form\ResetPasswordForm;
use DateTime;
use Del\Exception\EmailLinkException;
use Del\Exception\UserException;
use Del\Factory\CountryFactory;
use Del\Form\Field\Text\EmailAddress;
use Del\Service\UserService;
use Del\SessionManager;
use Del\Value\User\State;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;

class BoneMvcUserController extends Controller
{
    /** @var UserService $userService */
    private $userService;

    /** @var MailService $mailService */
    private $mailService;

    /**
     * BoneMvcUserController constructor.
     * @param UserService $userService
     * @param MailService $mailService
     */
    public function __construct(UserService $userService, MailService $mailService)
    {
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
        $body = $this->getView()->render('bonemvcuser::index', []);

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
        $form = new RegistrationForm('register', $this->getTranslator());
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
                    $mail->setSubject($this->getTranslator()->translate('email.user.register.thankswith', 'user') . ' ' . $this->mailService->getSiteConfig()->getTitle());
                    $mail->setTemplate('email.user::user_registration/user_registration');
                    $mail->setViewData([
                        'siteUrl' => $env->getSiteURL(),
                        'logo' => $this->getSiteConfig()->getEmailLogo(),
                        'activationLink' => '/user/activate/' . $email . '/' . $token,
                    ]);
                    $this->mailService->sendEmail($mail);
                    $body = $this->getView()->render('bonemvcuser::thanks-for-registering');

                    return new HtmlResponse($body);

                } catch (UserException $e) {
                    $message = [$e->getMessage(), 'danger'];
                }
            }
        }

        $body = $this->getView()->render('bonemvcuser::register', ['form' => $form, 'message' => $message]);

        return new HtmlResponse($body);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function activateAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $email = $args['email'];
        $token = $args['token'];
        $translator = $this->getTranslator();
        $userService = $this->userService;
        $message = null;

        try {

            $link = $userService->findEmailLink($email, $token);
            $user = $link->getUser();
            $user->setState(new State(State::STATE_ACTIVATED));
            $user->setLastLogin(new DateTime());
            $userService->saveUser($user);
            $userService->deleteEmailLink($link);
            SessionManager::set('user', $user->getId());

        } catch (EmailLinkException $e) {
            switch ($e->getMessage()) {
                case EmailLinkException::LINK_EXPIRED:
                    $message = [$translator->translate('login.activation.expired') . '<a href="/resend-activation-mail/' . $email . '">' . $translator->translate('login.activation.expired2') . '</a>', 'danger'];
                    break;
                default:
                    $message = [$e->getMessage(), 'danger'];
                    break;
            }
        }

        $body = $this->getView()->render('bonemvcuser::activate-user-account', ['message' => $message]);

        return new HtmlResponse($body);
    }


    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function loginAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $form = new LoginForm('userlogin', $this->getTranslator());

        $body = $this->getView()->render('bonemvcuser::login', ['form' => $form]);

        return new HtmlResponse($body);
    }


    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function loginFormAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $translator = $this->getTranslator();
        $form = new LoginForm('userlogin', $translator);
        $post = $request->getParsedBody();
        $form->populate($post);
        $params = ['form' => $form];

        try {

            if ($form->isValid()) {
                $data = $form->getValues();
                $email = $data['email'];
                $pass = $data['password'];
                $userId = $this->userService->authenticate($email, $pass);
                $locale = $translator->getLocale();
                SessionManager::set('user', $userId);
                SessionManager::set('locale', $locale);

                return new RedirectResponse('/' . $locale . '/user/home');
            }
        } catch (UserException $e) {
            switch ($e->getMessage()) {
                case UserException::USER_NOT_FOUND:
                case UserException::WRONG_PASSWORD:
                    $message = [$translator->translate('login.error.password', 'user') . '<a href="/user/lost-password/' . $email . '">' . $translator->translate('login.error.password2', 'user') . '</a>', 'danger'];
                    break;
                case UserException::USER_UNACTIVATED:
                    $message = [$translator->translate('login.unactivated', 'user') . '<a href="/user/resend-activation-mail/' . $email . '">' . $translator->translate('login.unactivated2', 'user') . '</a>', 'danger'];
                    break;
                case UserException::USER_DISABLED:
                case UserException::USER_BANNED:
                    $message = [$translator->translate('login.activation.banned', 'user'), 'danger'];
                    break;
                default:
                    $message = $e->getMessage();
                    break;
            }

            $params['message'] = $message;
        }

        $body = $this->getView()->render('bonemvcuser::login', $params);

        return new HtmlResponse($body);

    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function homePageAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $body = $this->getView()->render('bonemvcuser::home', [
            'message' => [$this->getTranslator()->translate('home.loggedin', 'user'), 'success'],
            'user' => $user,
        ]);

        return new HtmlResponse($body);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function logoutAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        SessionManager::destroySession();

        return new RedirectResponse(new Uri('/'));
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function resendActivationEmailAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $success = false;
        $email = $request->getAttribute('email');
        $user = $this->userService->findUserByEmail($email);
        $message = [];
        $translator = $this->getTranslator();

        if (!$user) {
            throw new Exception(UserException::USER_NOT_FOUND, 404);
        }

        if ($user->getState()->getValue() == State::STATE_ACTIVATED) {
            $message = [UserException::USER_ACTIVATED, 'danger'];
        } else {
            try {
                $link = $this->userService->generateEmailLink($user);
                $mail = $this->mailService;

                $env = $mail->getSiteConfig()->getEnvironment();
                $email = $user->getEmail();
                $token = $link->getToken();

                $mail = new EmailMessage();
                $mail->setTo($user->getEmail());
                $mail->setSubject($translator->translate('email.user.register.thankswith', 'user') . ' ' . $this->mailService->getSiteConfig()->getTitle());
                $mail->setTemplate('email.user::user_registration/user_registration');
                $mail->setViewData([
                    'siteUrl' => $env->getSiteURL(),
                    'logo' => $this->getSiteConfig()->getEmailLogo(),
                    'activationLink' => '/user/activate/' . $email . '/' . $token,
                ]);
                $this->mailService->sendEmail($mail);

            } catch (Exception $e) {
                $message = [$translator->translate('login.resendactivation.error', 'user')
                    . $this->getSiteConfig()->getContactEmail() . '', 'danger'];
            }
        }

        $body = $this->getView()->render('bonemvcuser::resend-activation', $message);

        return new HtmlResponse($body);
    }


    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function forgotPasswordAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $email = $request->getAttribute('email');

        $user = $this->userService->findUserByEmail($email);

        if (!$user) {
            throw new Exception(UserException::USER_NOT_FOUND, 404);
        }

        if ($user->getState()->getValue() == State::STATE_UNACTIVATED) {
            $this->forward('resend-activation-mail');
        }

        try {

            $link = $this->userService->generateEmailLink($user);
            $email = $user->getEmail();
            $token = $link->getToken();
            $env = $this->getSiteConfig()->getEnvironment();
            $mail = new EmailMessage();
            $mail->setTo($email);
            $mail->setSubject($this->getTranslator()->translate('email.forgotpass.subject', 'user') . $this->mailService->getSiteConfig()->getTitle() . '.');
            $mail->setTemplate('email.user::user_registration/reset_password');
            $mail->setViewData([
                'siteUrl' => $env->getSiteURL(),
                'logo' => $this->getSiteConfig()->getEmailLogo(),
                'resetLink' => '/user/reset-password/' . $email . '/' . $token,
            ]);
            $this->mailService->sendEmail($mail);


        } catch (Exception $e) {
            $this->view->message = [$e->getMessage(), 'danger'];
        }

        $body = $this->getView()->render('bonemvcuser::forgot-password');

        return new HtmlResponse($body);
    }


    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function resetPasswordAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $email = $request->getAttribute('email');
        $token = $request->getAttribute('token');
        $form = new ResetPasswordForm('resetpass');
        $translator = $translator->getTranslator();
        $params = [];
        $success = false;

        $user = $this->userService->findUserByEmail($email);
        if (!$user) {
            throw new Exception(UserException::USER_NOT_FOUND, 404);
        }

        try {
            $link = $this->userService->findEmailLink($email, $token);

            if ($request->getMethod() === 'POST') {

                $data = $request->getParsedBody();
                $form->populate($data);

                if ($form->isValid()) {
                    $data = $form->getValues();
                    if ($data['password'] === $data['confirm']) {
                        $this->userService->changePassword($user, $data['password']);
                        $this->userService->deleteEmailLink($link);
                        $message = [$translator > translate('email.resetpass.success', 'user'), 'success'];
                        $success = true;
                        SessionManager::set('user', $user->getId());
                    } else {
                        $message = [$translator > translate('email.resetpass.nomatch', 'user'), 'danger'];
                        $form = new ResetPasswordForm('resetpass');
                    }
                }
            }
        } catch (EmailLinkException $e) {
            $message = [$e->getMessage(), 'danger'];
        } catch (Exception $e) {
            throw $e;
        }

        if (isset($message)) {
            $params['message'] = $message;
        }
        $params['success'] = $success;
        $params['form'] = $form;
        $body = $this->getView()->render('bonemvcuser::reset-pass', $params);

        return new HtmlResponse($body);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function changePasswordAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $form = new ResetPasswordForm('resetpass');
        $translator = $translator->getTranslator();
        $params = [];
        $success = false;

        if ($request->getMethod() === 'POST') {

            $data = $request->getParsedBody();
            $form->populate($data);

            if ($form->isValid()) {
                $data = $form->getValues();
                if ($data['password'] === $data['confirm']) {
                    $this->userService->changePassword($user, $data['password']);
                    $message = [$translator > translate('email.resetpass.success', 'user'), 'success'];
                    $success = true;
                } else {
                    $message = [$translator > translate('email.resetpass.nomatch', 'user') , 'danger'];
                    $form = new ResetPasswordForm('resetpass');
                }
            }
        }

        if (isset($message)) {
            $params['message'] = $message;
        }
        $params['success'] = $success;
        $params['form'] = $form;

        $body = $this->getView()->render('bonemvcuser::change-pass', $params);

        return new HtmlResponse($body);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function changeEmailAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $form = new LoginForm('changeemail', $this->getTranslator());
        $form->getField('email')->setLabel('New email');
        $form->getField('submit')->setValue('Submit');
        $translator = $this->getTranslator();
        $params = [
            'form' => $form
        ];

        if ($request->getMethod() === 'POST') {

            $data = $request->getParsedBody();
            $form->populate($data);

            if ($form->isValid($data)) {

                $newEmail = $form->getField('email')->getValue();
                $password = $form->getField('password')->getValue();

                $existing = $this->userService->findUserByEmail($newEmail);

                if ($existing) {
                    $message = [$translator > translate('email.changeemail.registered', 'user') . 'This email already has a registered account with ' . $this->getSiteConfig()->getTitle() . '.', 'danger'];
                } else {
                    if ($this->userService->checkPassword($user, $password)) {

                        $link = $this->userService->generateEmailLink($user);

                        try {

                            $link = $this->userService->generateEmailLink($user);
                            $email = $user->getEmail();
                            $token = $link->getToken();
                            $env = $this->getSiteConfig()->getEnvironment();
                            $mail = new EmailMessage();
                            $mail->setTo($email);
                            $mail->setSubject($translator > translate('email.changeemail.subject', 'user') . $this->mailService->getSiteConfig()->getTitle() . '.');
                            $mail->setTemplate('email.user::user_registration/change_email');
                            $mail->setViewData([
                                'siteUrl' => $env->getSiteURL(),
                                'logo' => $this->getSiteConfig()->getEmailLogo(),
                                'resetLink' => '/user/reset-email/' . $email . '/' . $newEmail . '/' . $token,
                            ]);
                            $this->mailService->sendEmail($mail);
                            $message = [$translator > translate('email.changeemail.sent', 'user'), 'info'];
                            unset ($params['form']);

                        } catch (Exception $e) {
                            $message = [$translator > translate('email.changeemail.notsent', 'user') . $this->config->email->support . '.', 'danger'];
                        }

                    } else {
                        $message = [$translator > translate('email.changeemail.wrongpass', 'user'), 'danger'];
                    }
                }
            }
            $params['message'] = $message;
        }

        $body = $this->getView()->render('bonemvcuser::change-email', $params);

        return new HtmlResponse($body);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function editProfileAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $person = $user->getPerson();
        $form = new PersonForm('profile', $this->getTranslator());
        $array = $this->userService->getPersonSvc()->toArray($person);

        $form->populate($array);

        if ($request->getMethod() === 'POST') {
            $post = $request->getParsedBody();
            $form->populate($post);
            if ($form->isValid()) {
                $data = $form->getValues();
                $dateFormat = $this->getSiteConfig()->getAttribute('i18n')['date_format'];
                $data['dob'] = DateTime::createFromFormat($dateFormat, $data['dob']);
                $data['country'] = CountryFactory::generate($data['country']);
                $this->userService->getPersonSvc()->populateFromArray($person, $data);
                $this->userService->saveUser($user);
            }
        }

        $body = $this->getView()->render('bonemvcuser::edit-profile', ['person' => $person, 'form' => $form->render()]);

        return new HtmlResponse($body);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $args
     * @return ResponseInterface
     */
    public function resetEmailAction(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $email = $request->getAttribute('email');
        $newEmail = $request->getAttribute('new-email');
        $token = $request->getAttribute('token');
        $message = null;
        $translator = $this->getTranslator();

        try {

            $link = $this->userService->findEmailLink($email, $token);
            $user = $link->getUser();
            $user->setEmail($newEmail);
            $this->userService->saveUser($user);
            $this->userService->deleteEmailLink($link);
            $message = [$translator->translate('email.changeemail.success', 'user') . $newEmail . $translator->translate('email.changeemail.success2', 'user'), 'success'];
            SessionManager::set('user', $user->getId());

        } catch (EmailLinkException $e) {
            $message = [$e->getMessage(), 'danger'];
        } catch (Exception $e) {
            throw $e;
        }

        $body = $this->getView()->render('bonemvcuser::reset-email', ['message' => null]);

        return new HtmlResponse($body);
    }
}
