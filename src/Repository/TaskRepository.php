<?php

namespace App\Repository;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Enum\TaskPriority;
use App\Entity\Enum\TaskStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Task::class);
    }

    public function getReference(Uuid $id): ?Task
    {
        return $this->getEntityManager()->getReference($this->getEntityName(), $id);
    }

    /**
     * @return array|Task[]
     */
    public function getList(): array
    {
        return $this->findBy([], ['updatedDate' => 'DESC']);
    }

    /**
     * @return iterable<Task>
     */
    public function getProjectTasks(Project $project): iterable
    {
        $qb = $this
            ->createQueryBuilder('t')
            ->andWhere('t.project = :project');

        $qb->addOrderBy('t.updatedDate', 'DESC');

        $qb->setParameter('project', $project->getId()->toBinary(), 'uuid');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return iterable<Task>
     */
    public function getAssignedTasks(User $user): iterable
    {
        $qb = $this
            ->createQueryBuilder('t')
            ->andWhere('t.worker = :worker');

        $qb->setParameter('worker', Uuid::fromString($user->getId())->toBinary());

        $qb->add('orderBy', 'FIELD(t.priority, :priorities), FIELD(t.status, :statuses)');
        $qb->setParameter('statuses', [
            TaskStatus::ACTIVE,
            TaskStatus::PENDING,
            TaskStatus::COMPLETE,
        ]);
        $qb->setParameter('priorities', [
            TaskPriority::HIGH,
            TaskPriority::MEDIUM,
            TaskPriority::LOW,
        ]);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return iterable<Task>
     */
    public function getCreatedTasks(User $user): iterable
    {
        $qb = $this
            ->createQueryBuilder('t')
            ->andWhere('t.creator = :creator');

        $qb->addOrderBy('t.updatedDate', 'DESC');

        $qb->setParameter('creator', Uuid::fromString($user->getId())->toBinary());

        return $qb->getQuery()->getResult();
    }
}
