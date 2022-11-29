<?php

namespace App\Manager;

use App\Data\UserAddData;
use App\Entity\User;
use App\Entity\Factory\UserFactory;
use App\Manager\AppEntityManager;
use App\Repository\UserRepository;
use App\Service\PasswordHasherService;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserManager
{
    public function __construct(
        private readonly AppEntityManager $aem,
        private readonly UserRepository $userRepository,
        private readonly PasswordHasherService $passwordHasher,
    ) {
    }

    public function userExists(string $email): bool
    {
        $user = $this->userRepository->getUserByEmail($email);

        if (!$user) {
            return false;
        }

        return true;
    }

    public function updatePassword(User $user, string $password): void
    {
        $user->setPassword($this->passwordHasher->hash($password));
        $this->aem->refresh();
    }

    /**
     * @param array<string> $roles
     */
    public function updateRoles(User $user, array $roles): void
    {
        $user->setRoles($roles);
        $this->aem->refresh();
    }

    public function createFromData(UserAddData $data): void
    {
        $user = UserFactory::create();
        $this->buildFromData($user, $data);
        $this->aem->save($user);
    }

    private function buildFromData(User $user, UserAddData $data): void
    {
        $user
            ->setEmail($data->email)
            ->setPassword($this->passwordHasher->hash($data->password))
            ->setRoles($data->roles);
    }
}
