<?php

declare(strict_types=1);

namespace Bone\User\Http\Controller\Api;
use Bone\App\Service\PersonService;
use Bone\Http\Controller\ApiController;

class PersonApiController extends ApiController
{
    public function getServiceClass(): string
    {
        return PersonService::class;
    }
}
