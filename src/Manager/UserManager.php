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

    public function updatePassword(User $user, string $password): User
    {
        $user->setPassword($this->passwordHasher->hash($password));
        $this->aem->flush();
        return $user;
    }

    /**
     * @param array<string> $roles
     */
    public function updateRoles(User $user, array $roles): User
    {
        $user->setRoles($roles);
        $this->aem->flush();
        return $user;
    }

    public function createFromData(
        UserAddData $data,
        bool $save = true,
    ): User
    {
        $user = UserFactory::create();
        $this->buildFromData($user, $data);

        if ($save) {
            $this->aem->save($user);
        }

        return $user;
    }

    private function buildFromData(User $user, UserAddData $data): void
    {
        $user
            ->setEmail($data->email)
            ->setPassword($this->passwordHasher->hash($data->password))
            ->setRoles($data->roles);
    }
}
