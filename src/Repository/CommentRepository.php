<?php

namespace App\Repository;

use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Comment>
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry
    ) {
        parent::__construct($registry, Comment::class);
    }

    public function getReference(Uuid $id): ?Comment
    {
        return $this->getEntityManager()->getReference($this->getEntityName(), $id);
    }

    /**
     * @return array|Comment[]
     */
    public function getList(): array
    {
        return $this->findBy([], ['createdDate' => 'DESC']);
    }

    /**
     * @return iterable<Comment>
     */
    public function getTaskComments(Task $task): iterable
    {
        $qb = $this
          ->createQueryBuilder('c')
          ->andWhere('c.task = :task');

        $qb->addOrderBy('c.updatedDate', 'DESC');

        $qb->setParameter('task', Uuid::fromString($task->getId())->toBinary());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return iterable<Comment>
     */
    public function getUserComments(User $user): iterable
    {
        $qb = $this
          ->createQueryBuilder('c')
          ->andWhere('c.author = :author');

        $qb->addOrderBy('c.createdDate', 'DESC');

        $qb->setParameter('author', Uuid::fromString($user->getId())->toBinary());

        return $qb->getQuery()->getResult();
    }
}
