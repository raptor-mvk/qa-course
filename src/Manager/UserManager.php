<?php

namespace App\Manager;

use App\Entity\User;
use App\Form\LinkedUserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use SaveUserDTO;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class UserManager
{
    private EntityManagerInterface $entityManager;

    private FormFactoryInterface $formFactory;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formFactory)
    {
        $this->entityManager = $entityManager;
        $this->formFactory = $formFactory;
    }

    public function getSaveForm(): FormInterface
    {
        return $this->formFactory->createBuilder(FormType::class)
            ->add('login', TextType::class)
            ->add('password', PasswordType::class)
            ->add('age', IntegerType::class)
            ->add('isActual', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class)
            ->getForm();
    }

    public function saveUserFromDTO(User $user, SaveUserDTO $saveUserDTO): ?int
    {
        $user->setLogin($saveUserDTO->login);
        $user->setPassword($saveUserDTO->password);
        $user->setAge($saveUserDTO->age);
        $user->setIsActive($saveUserDTO->isActive);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user->getId();
    }

    public function getUpdateForm(int $userId): ?FormInterface
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->find($userId);
        if ($user === null) {
            return null;
        }

        return $this->formFactory->createBuilder(FormType::class, SaveUserDTO::fromEntity($user))
            ->add('login', TextType::class)
            ->add('password', PasswordType::class)
            ->add('age', IntegerType::class)
            ->add('isActive', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class)
            ->add('followers', CollectionType::class, [
                'entry_type' => LinkedUserType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
            ])
            ->setMethod('PATCH')
            ->getForm();
    }

    public function updateUserFromDTO(int $userId, SaveUserDTO $userDTO): bool
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->find($userId);
        if ($user === null) {
            return false;
        }

        foreach ($userDTO->followers as $followerData) {
            $followerUserDTO = new SaveUserDTO($followerData);
            /** @var User $followerUser */
            if (isset($followerData['id'])) {
                $followerUser = $userRepository->find($followerData['id']);
                if ($followerUser === null) {
                    $this->saveUserFromDTO($followerUser, $followerUserDTO);
                }
            } else {
                $followerUser = new User();
                $this->saveUserFromDTO($followerUser, $followerUserDTO);
                $user->addFollower($followerUser);
            }
        }

        return $this->saveUserFromDTO($user, $userDTO);
    }

    public function saveUser(string $login): ?int
    {
        $user = new User();
        $user->setLogin($login);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user->getId();
    }

    public function updateUser(int $userId, string $login): ?User
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->find($userId);
        if ($user === null) {
            return null;
        }
        $user->setLogin($login);
        $this->entityManager->flush();

        return $user;
    }

    public function deleteUser(User $user): bool
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return true;
    }

    public function deleteUserById(int $userId): bool
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);
        /** @var User $user */
        $user = $userRepository->find($userId);
        if ($user === null) {
            return false;
        }

        return $this->deleteUser($user);
    }

    /**
     * @return User[]
     */
    public function getUsers(int $page, int $perPage): array
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        return $userRepository->getUsers($page, $perPage);
    }
}
