<?php

namespace App\Controller\Api\v1;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TasksController extends AbstractFOSRestController
{
    /**
     * @Rest\Get("/api/v1/tasks")
     */
    public function apiTasksAction(Request $request): Response
    {
        $user = $this->getUser();

        return new JsonResponse(
            [
                'username' => $user === null ? 'No authenticated user' : $user->getUserIdentifier(),
                'roles' => $user === null ? 'No authenticated user' : $user->getRoles(),
                'tasks' => 'No tasks found for user',
            ]
        );
    }

    /**
     * @Rest\Get("/server-api/v1/tasks")
     */
    public function serverApiTasksAction(Request $request): Response
    {
        return new JsonResponse('',
            Response::HTTP_NO_CONTENT
        );
    }
}
