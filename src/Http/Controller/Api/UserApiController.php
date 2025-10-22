<?php

declare(strict_types=1);

namespace Bone\User\Http\Controller\Api;

use Bone\Http\Controller\ApiController;
use Del\Service\UserService;

class UserApiController extends ApiController
{
    public function getServiceClass(): string
    {
        return UserService::class;
    }
}
