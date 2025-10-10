<?php

declare(strict_types=1);

namespace Bone\User\Http\Controller\Admin;

use Bone\BoneDoctrine\Http\Controller\AdminController;
use Del\Person\Service\PersonService;
use Del\Person\Entity\Person;

class PersonAdminController extends AdminController
{
    public function getServiceClass(): string
    {
        return PersonService::class;
    }

    public function getEntityClass(): string
    {
        return Person::class;
    }
}
