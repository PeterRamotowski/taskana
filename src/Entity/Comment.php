<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Trait\EntityTimestampableTrait;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Loggable;
use Gedmo\Mapping\Annotation\Versioned;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: \App\Repository\CommentRepository::class)]
#[Table(name: 'comments')]
#[Loggable]
class Comment implements EntityInterface
{
    use EntityTimestampableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    private string $id;

    #[Column(type: 'text', nullable: false)]
    #[Versioned]
    private string $description;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'comments')]
    #[JoinColumn(name: 'author', nullable: false)]
    #[Blameable(on: 'create')]
    #[Versioned]
    private User $author;

    #[ManyToOne(targetEntity: Task::class, inversedBy: 'comments')]
    #[JoinColumn(name: 'task', nullable: true)]
    #[Versioned]
    private ?Task $task;

    public function __construct() {}

    public function getId(): Uuid
    {
        return Uuid::fromString($this->id);
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getTask(): ?Task
    {
        return $this->task;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;
        return $this;
    }
}
