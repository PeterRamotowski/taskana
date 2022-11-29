<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Project>
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Project::class);
    }

    public function getReference(Uuid $id): ?Project
    {
        return $this->getEntityManager()->getReference($this->getEntityName(), $id);
    }

    /**
     * @return array|Project[]
     */
    public function getList(): array
    {
        return $this->findBy([], ['title' => 'ASC']);
    }
}
