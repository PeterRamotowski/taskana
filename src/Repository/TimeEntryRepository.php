<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\TimeEntry;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeEntry>
 *
 * @method TimeEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeEntry[]    findAll()
 * @method TimeEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeEntry::class);
    }

    /**
     * Find active (running) time entry for a user and task
     */
    public function findActiveEntry(User $user, Task $task): ?TimeEntry
    {
        return $this->createQueryBuilder('te')
            ->where('te.user = :user')
            ->andWhere('IDENTITY(te.task) = :taskId')
            ->andWhere('te.endTime IS NULL')
            ->setParameter('user', $user)
            ->setParameter('taskId', $task->getId()->toBinary(), 'uuid')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get total time spent on a task (in seconds)
     */
    public function getTotalTimeForTask(Task $task): int
    {
        $result = $this->createQueryBuilder('te')
            ->select('SUM(te.duration) as total')
            ->where('IDENTITY(te.task) = :taskId')
            ->andWhere('te.endTime IS NOT NULL')
            ->setParameter('taskId', $task->getId()->toBinary(), 'uuid')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Get time entries for a task
     *
     * @return TimeEntry[]
     */
    public function findByTask(Task $task, ?int $limit = null): array
    {
        $qb = $this->createQueryBuilder('te')
            ->where('IDENTITY(te.task) = :taskId')
            ->setParameter('taskId', $task->getId()->toBinary(), 'uuid')
            ->orderBy('te.startTime', 'DESC');

        if ($limit !== null) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get time entries for a user within a date range
     *
     * @return TimeEntry[]
     */
    public function findByUserAndDateRange(
        User $user,
        \DateTime $startDate,
        \DateTime $endDate
    ): array {
        $start = clone $startDate;
        $start->setTime(0, 0, 0);

        $end = clone $endDate;
        $end->setTime(23, 59, 59);
        
        return $this->createQueryBuilder('te')
            ->where('IDENTITY(te.user) = :userId')
            ->andWhere('te.startTime BETWEEN :startDate AND :endDate')
            ->setParameter('userId', $user->getId()->toBinary(), 'uuid')
            ->setParameter('startDate', $start)
            ->setParameter('endDate', $end)
            ->orderBy('te.startTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all active (running) time entries for a user
     *
     * @return TimeEntry[]
     */
    public function findActiveEntriesForUser(User $user): array
    {
        return $this->createQueryBuilder('te')
            ->where('te.user = :user')
            ->andWhere('te.endTime IS NULL')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find the active timer for a user (any task)
     */
    public function findActiveTimerForUser(User $user): ?TimeEntry
    {
        return $this->createQueryBuilder('te')
            ->where('te.user = :user')
            ->andWhere('te.endTime IS NULL')
            ->setParameter('user', $user)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
