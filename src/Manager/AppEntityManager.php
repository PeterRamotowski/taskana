<?php

namespace App\Manager;

use App\Entity\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;

class AppEntityManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @param EntityManagerInterface $em 
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function refresh(): void
    {
        $this->em->flush();
    }

    /**
     * @param EntityInterface $entity
     */
    public function preSave(EntityInterface $entity): void
    {
        $this->em->persist($entity);
    }

    /**
     * @param EntityInterface $entity 
     */
    public function save(EntityInterface $entity): void
    {
        $this->preSave($entity);
        $this->refresh();
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
        $this->refresh();
    }

    /**
     * @param iterable<EntityInterface> $entities
     */
    public function removeAll(iterable $entities): void
    {
        foreach ($entities as $entity) {
            $this->preRemove($entity);
        }
        $this->refresh();
    }
}
