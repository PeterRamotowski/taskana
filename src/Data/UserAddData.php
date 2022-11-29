<?php

namespace App\Data;

use App\Validator\UserEmailNotExists;
use Symfony\Component\Validator\Constraints as Assert;

class UserAddData
{
    #[Assert\NotBlank]
    #[Assert\Email]
    #[UserEmailNotExists]
    public string $email;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $password;

    /**
     * @var array<string> $roles
     */
    #[Assert\NotBlank]
    #[Assert\Type('array')]
    public array $roles;
}
