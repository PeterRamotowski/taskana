<?php

namespace App\Manager;

use App\Entity\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;

class AppEntityManager
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function flush(): void
    {
        $this->em->flush();
    }

    /**
     * @param EntityInterface $entity
     */
    public function persist(EntityInterface $entity): void
    {
        $this->em->persist($entity);
    }

    /**
     * @param EntityInterface $entity 
     */
    public function save(EntityInterface $entity): void
    {
        $this->persist($entity);
        $this->flush();
    }

    /**
     * @param EntityInterface $entity
     */
    public function preRemove(EntityInterface $entity): void
    {
        $this->em->remove($entity);
    }

    /**
     * @param EntityInterface $entity
     */
    public function remove(EntityInterface $entity): void
    {
        $this->preRemove($entity);
        $this->flush();
    }

    /**
     * @param iterable<EntityInterface> $entities
     */
    public function removeAll(iterable $entities): void
    {
        foreach ($entities as $entity) {
            $this->preRemove($entity);
        }
        $this->flush();
    }
}
