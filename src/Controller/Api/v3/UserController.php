<?php

namespace App\Controller\Api\v3;

use App\Manager\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/api/v3/user")
 */
class UserController extends AbstractController
{
    private UserManager $userManager;

    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(UserManager $userManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->userManager = $userManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @Route("", methods={"DELETE"})
     */
    public function deleteUserAction(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $user = $this->userManager->findUserById($userId);
        $result = $this->userManager->deleteUserById($userId);

        return new JsonResponse(['success' => $result], $result ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    }
}
