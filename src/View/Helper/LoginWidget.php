<?php declare(strict_types=1);

namespace Bone\User\View\Helper;

use Del\Image;
use Del\Service\UserService;
use Del\SessionManager;
use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Laminas\I18n\Translator\Translator;
use League\Plates\Template\Template;

class LoginWidget implements ExtensionInterface
{
    public ?Template $template = null;

    public function __construct(
        private UserService $userService,
        private Translator $translator,
        private SessionManager $sessionManager,
        private string $uploadFolder
    ) {
    }

    /**
     * @param Engine $engine
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction('loginWidget', [$this, 'loginWidget']);
    }

    /**
     * @return string
     * @throws Image\Exception\NothingLoadedException
     */
    public function loginWidget(): string
    {
        $locale = $this->translator->getLocale();

        if ($id = $this->sessionManager->get('user')) {
            $user = $this->userService->findUserById($id);
            $person = $user->getPerson();
            $html = '<li class="nav-item dropdown">';

            if ($person->getImage()) {
                $image = new Image($this->uploadFolder . $person->getImage());
                $html .= '<a id="user-dropdown" href="#" class="nav-link dropdown-toggle avatar" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                            <img id="user-avatar" src="' . $image->outputBase64Src() . '" class="img-rounded img-circle" />';
                $html .=  $person->getAka() ?: $user->getEmail() . '</a>';
            } else {
                $html .= '<a id="user-dropdown" href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $user->getEmail() . '</a>';
            }

            $html .=  '<div class="dropdown-menu login-widget" aria-labelledby="user-dropdown">
                            <a class="dropdown-item" href="/' . $locale . '/user/change-email">Change Email</a>
                            <a class="dropdown-item" href="/' . $locale . '/user/change-password">Change Password</a>
                            <a class="dropdown-item" href="/' . $locale . '/user/edit-profile">Edit Profile</a>
                            <a class="dropdown-item" href="/' . $locale . '/user/logout">Logout</a>
                        </div>
                    </li>';

            return $html;

        }

        return '<li class="nav-item"><a class="nav-link" href="/' . $locale . '/user/login">Login</a></li>';

    }


}
