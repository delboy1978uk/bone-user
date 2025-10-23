<?php

declare(strict_types=1);

namespace Bone\User\Http\Controller\Api;

use Bone\Http\Controller\ApiController;
use Del\Person\Service\PersonService;

class PersonApiController extends ApiController
{
    public function getServiceClass(): string
    {
        return PersonService::class;
    }
}
