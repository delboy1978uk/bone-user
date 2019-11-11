<?php declare(strict_types=1);

namespace BoneMvc\Module\BoneMvcUser\View\Helper;

use Del\Service\UserService;
use Del\SessionManager;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Zend\I18n\Translator\Translator;

class LoginWidget implements ExtensionInterface
{
    private $userService;

    private $translator;

    /**
     * LoginWidget constructor.
     * @param UserService $userService
     * @param Translator $translator
     */
    public function __construct(UserService $userService, Translator $translator)
    {
        $this->userService = $userService;
        $this->translator = $translator;
    }

    /**
     * @param Engine $engine
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction('loginWidget', [$this, 'loginWidget']);
    }


    public function loginWidget()
    {
        $locale = $this->translator->getLocale();

        if ($id = SessionManager::get('user')) {
            $user = $this->userService->findUserById($id);
            $person = $user->getPerson();
            $html = '<li class="dropdown">';
            if ($person->getImage()) {
                $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <img id="user-avatar" src="/download?file=' . $person->getImage() . '" class="navatar img-responsive img-circle pull-left" />';
                $html .=  $person->getAka() ?: $user->getEmail();
                $html .= '<span class="caret"></span></a>';
            } else {
                $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $user->getEmail() . ' <span class="caret"></span></a>';
            }

            $html .= '<ul class="dropdown-menu rampant-dropdown">
                            <li><a href="/' . $locale . '/user/change-email">Change Email</a></li>
                            <li><a href="/' . $locale . '/user/change-password">Change Password</a></li>
                            <li><a href="/' . $locale . '/user/edit-profile">Edit Profile</a></li>
                            <li class="divider"></li>
                            <li><a href="/' . $locale . '/user/logout">Logout</a></li>
                        </ul>
                    </li>';

            return $html;
        } else {
            return '<li><a href="' . $locale . '/user/login">Login</a></li>';
        }
    }
    

}


/*


