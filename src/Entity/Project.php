<?php

namespace App\Entity;

use App\Entity\Trait\EntityTimestampableTrait;
use App\EventListener\ProjectListener;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\EntityListeners;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Loggable;
use Gedmo\Mapping\Annotation\Versioned;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: ProjectRepository::class)]
#[Table(name: 'projects')]
#[EntityListeners([ProjectListener::class])]
#[Loggable]
class Project implements EntityInterface
{
    use EntityTimestampableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    private string $id;

    #[Column(type: 'string', nullable: false)]
    #[Versioned]
    private string $title;

    #[Column(type: 'text', nullable: true)]
    #[Versioned]
    private ?string $description;

    #[Column(type: 'boolean', nullable: false, options: ['default' => false])]
    #[Versioned]
    private bool $archived;

    /**
     * @var Collection|Task[]
     */
    #[OneToMany(targetEntity: Task::class, mappedBy: 'project')]
    private Collection $tasks;

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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function archive(): self
    {
        $this->archived = true;
        return $this;
    }

    public function unarchive(): self
    {
        $this->archived = false;
        return $this;
    }

    public function isArchived(): bool
    {
        return $this->archived;
    }

    /**
     * @return Collection|Task[]
     */
    public function getTasks(): Collection
    {
      return $this->tasks;
    }

    /**
     * @param Task $task 
     */
    public function addTask(Task $task): self
    {
      $this->tasks[] = $task;
      return $this;
    }
}
