<?php

namespace App\Controller\Api\v1;

use App\Entity\User;
use App\Event\CreateUserEvent;
use App\Exception\DeprecatedApiException;
use App\Manager\UserManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use App\DTO\SaveUserDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

/**
 * @Route("/api/v1/user")
 */
class UserController extends AbstractController
{
    private UserManager $userManager;

    private EventDispatcherInterface $eventDispatcher;

    private Environment $twig;

    public function __construct(UserManager $userManager, EventDispatcherInterface $eventDispatcher, Environment $twig)
    {
        $this->userManager = $userManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function saveUserAction(Request $request): Response
    {
        throw new DeprecatedApiException('This API method is deprecated');

        $login = $request->request->get('login');
        $userId = $this->userManager->saveUser($login);
        [$data, $code] = $userId === null ?
            [['success' => false], 400] :
            [['success' => true, 'userId' => $userId], 200];

        return new JsonResponse($data, $code);
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getUsersAction(Request $request): Response
    {
        $perPage = $request->query->get('perPage');
        $page = $request->query->get('page');
        $users = $this->userManager->getUsers($page ?? 0, $perPage ?? 20);
        $code = empty($users) ? 204 : 200;

        return new JsonResponse(['users' => array_map(static fn(User $user) => $user->toArray(), $users)], $code);
    }

    /**
     * @Route("", methods={"DELETE"})
     */
    public function deleteUserAction(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $result = $this->userManager->deleteUserById($userId);

        return new JsonResponse(['success' => $result], $result ? 200 : 404);
    }

    /**
     * @Route("", methods={"PATCH"})
     */
    public function updateUserAction(Request $request): Response
    {
        $userId = $request->query->get('userId');
        $login = $request->query->get('login');
        $result = $this->userManager->updateUser($userId, $login);

        return new JsonResponse(['success' => $result !== null], ($result !== null) ? 200 : 404);
    }

    /**
     * @Route("/{id}", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function deleteUserByIdAction(int $id): Response
    {
        $result = $this->userManager->deleteUserById($id);

        return new JsonResponse(['success' => $result], $result ? 200 : 404);
    }

    /**
     * @Route("/async", methods={"POST"})
     */
    public function saveUserAsyncAction(Request $request): Response
    {
        $this->eventDispatcher->dispatch(new CreateUserEvent($request->request->get('login')));

        return new JsonResponse(['success' => true], Response::HTTP_ACCEPTED);
    }

    /**
     * @Route("/form", methods={"GET"})
     */
    public function getSaveFormAction(): Response
    {
        $form = $this->userManager->getSaveForm();
        $content = $this->twig->render('form.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }

    /**
     * @Route("/form", methods={"POST"})
     */
    public function saveUserFormAction(Request $request): Response
    {
        $form = $this->userManager->getSaveForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userId = $this->userManager->saveUserFromDTO(new User(), new SaveUserDTO($form->getData()));
            [$data, $code] = ($userId === null) ? [['success' => false], 400] : [['id' => $userId], 200];

            return new JsonResponse($data, $code);
        }
        $content = $this->twig->render('form.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }

    /**
     * @Route("/form/{id}", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function getUpdateFormAction(int $id): Response
    {
        $form = $this->userManager->getUpdateForm($id);
        if ($form === null) {
            return new JsonResponse(['message' => "User with ID $id not found"], 404);
        }
        $content = $this->twig->render('form.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }

    /**
     * @Route("/form/{id}", methods={"PATCH"}, requirements={"id":"\d+"})
     */
    public function updateUserFormAction(Request $request, int $id): Response
    {
        $form = $this->userManager->getUpdateForm($id);
        if ($form === null) {
            return new JsonResponse(['message' => "User with ID $id not found"], 404);
        }

        /** @var SaveUserDTO $formData */
        $formData = $form->getData();
        $formData->followers = [];
        $form->setData($formData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $result = $this->userManager->updateUserFromDTO($id, $form->getData());

            return new JsonResponse(['success' => $result], $result ? 200 : 400);
        }
        $content = $this->twig->render('form.twig', [
            'form' => $form->createView(),
        ]);

        return new Response($content);
    }
}
