<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, User::class);
    }

    public function getReference(Uuid $id): ?User
    {
        return $this->getEntityManager()->getReference($this->getEntityName(), $id);
    }

    public function loadUserByIdentifier(string $identifier): ?User
    {
        return $this->findOneBy(['email' => $identifier]);
    }

    public function getUserByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * @return array|User[]
     */
    public function getList(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }
}
