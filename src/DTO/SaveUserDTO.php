<?php

use App\Entity\User;
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

    public function __construct(array $data)
    {
        $this->login = $data['login'] ?? '';
        $this->password = $data['login'] ?? '';
        $this->age = $data['age'] ?? 0;
        $this->isActive = $data['isActive'] ?? false;
    }

    public static function fromEntity(User $user): self
    {
        return new self([
            'login' => $user->getLogin(),
            'password' => $user->getPassword(),
            'age' => $user->getAge(),
            'isActive' => $user->isActive(),
        ]);
    }

}