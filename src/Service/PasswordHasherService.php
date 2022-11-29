<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class PasswordHasherService
{
    public function __construct(
        private readonly PasswordHasherFactoryInterface $passwordFactory,
    ) {
    }

    public function verify(string $encodedPassword, string $password): bool
    {
        return $this->getPasswordHasher()->verify($encodedPassword, $password);
    }

    public function hash(string $password): string
    {
        return $this->getPasswordHasher()->hash($password);
    }

    private function getPasswordHasher(): PasswordHasherInterface
    {
        return $this->passwordFactory->getPasswordHasher(User::class);
    }
}