<?php

namespace App\Tests\Manager;

use App\Entity\EntityInterface;
use App\Manager\AppEntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AppEntityManagerTest extends TestCase
{
    private AppEntityManager $appEntityManager;
    private EntityManagerInterface&MockObject $em;
    private EntityInterface&MockObject $entity;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->appEntityManager = new AppEntityManager($this->em);
        $this->entity = $this->createMock(EntityInterface::class);
    }

    public function testFlush(): void
    {
        $this->em->expects($this->once())->method('flush');
        $this->appEntityManager->flush();
    }

    public function testPersist(): void
    {
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->entity);
        $this->appEntityManager->persist($this->entity);
    }

    public function testSave(): void
    {
        $this->em->expects($this->once())
            ->method('persist')
            ->with($this->entity);
        $this->em->expects($this->once())
            ->method('flush');
        $this->appEntityManager->save($this->entity);
    }

    public function testPreRemove(): void
    {
        $this->em->expects($this->once())
            ->method('remove')
            ->with($this->entity);
        $this->appEntityManager->preRemove($this->entity);
    }

    public function testRemove(): void
    {
        $this->em->expects($this->once())
            ->method('remove')
            ->with($this->entity);
        $this->em->expects($this->once())
            ->method('flush');
        $this->appEntityManager->remove($this->entity);
    }

    public function testRemoveAll(): void
    {
        $entity2 = $this->createMock(EntityInterface::class);
        $this->em->expects($this->exactly(2))
            ->method('remove')
            ->withConsecutive([$this->entity], [$entity2]);
        $this->em->expects($this->once())
            ->method('flush');
        $this->appEntityManager->removeAll([$this->entity, $entity2]);
    }
}
