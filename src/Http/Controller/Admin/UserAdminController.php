<?php

declare(strict_types=1);

namespace Bone\User\Http\Controller\Admin;

use Bone\BoneDoctrine\Http\Controller\AdminController;
use Del\Entity\User;
use Del\Person\Service\PersonService;
use Del\Person\Entity\Person;
use Del\Service\UserService;

class UserAdminController extends AdminController
{
    public function getServiceClass(): string
    {
        return UserService::class;
    }

    public function getEntityClass(): string
    {
        return User::class;
    }
}
