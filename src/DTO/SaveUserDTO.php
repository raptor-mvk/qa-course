<?php

namespace App\DTO;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class SaveUserDTO
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=32)
     */
    public string $login;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=32)
     */
    public string $password;

    /**
     * @Assert\NotBlank()
     */
    public int $age;

    public bool $isActive;

    /**
     * @Assert\Type("array<SaveUserDTO>")
     * @Assert\Valid
     */
    public array $followers;

    /** @var string[] */
    public array $roles;

    public function __construct(array $data)
    {
        $this->login = $data['login'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->age = $data['age'] ?? 0;
        $this->isActive = $data['isActive'] ?? false;
        $this->roles = $data['roles'] ?? [];
        $this->followers = $data['followers'] ?? [];
    }

    public static function fromEntity(User $user): self
    {
        return new self([
            'login' => $user->getLogin(),
            'password' => $user->getPassword(),
            'age' => $user->getAge(),
            'isActive' => $user->isActive(),
            'roles' => $user->getRoles(),
            'followers' => array_map(
                static function (User $user) {
                    return [
                        'id' => $user->getId(),
                        'login' => $user->getLogin(),
                        'password' => $user->getPassword(),
                        'age' => $user->getAge(),
                        'isActive' => $user->isActive(),
                    ];
                },
                $user->getFollowers()
            ),
        ]);
    }

    public static function fromRequest(Request $request): self
    {
        $roles = $request->request->get('roles') ?? $request->query->get('roles');

        return new self(
            [
                'login' => $request->request->get('login') ?? $request->query->get('login'),
                'password' => $request->request->get('password') ?? $request->query->get('password'),
                'roles' => json_decode($roles, true, 512, JSON_THROW_ON_ERROR),
            ]
        );
    }
}