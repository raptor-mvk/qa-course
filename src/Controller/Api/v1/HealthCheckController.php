<?php

namespace App\Controller\Api\v1;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthCheckController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/api/v1/health-check")
     */
    public function healthCheckAction(Request $request): Response
    {
        $user = $this->getUser();

        return new JsonResponse(
            [
                'status' => 'OK',
                'username' => $user === null ? 'No authenticated user' : $user->getUserIdentifier(),
                'roles' => $user === null ? 'No authenticated user' : $user->getRoles(),
            ]
        );
    }
}
