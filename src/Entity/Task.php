<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Enum\TaskPriority;
use App\Entity\Enum\TaskStatus;
use App\Entity\Trait\EntityTimestampableTrait;
use App\EventListener\TaskListener;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\EntityListeners;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Blameable;
use Gedmo\Mapping\Annotation\Loggable;
use Gedmo\Mapping\Annotation\Versioned;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: TaskRepository::class)]
#[Table(name: 'tasks')]
#[EntityListeners([TaskListener::class])]
#[Loggable]
class Task implements EntityInterface
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
    private ?string $description = null;

    #[Column(type: "string", enumType: TaskPriority::class, options: ['default' => TaskPriority::MEDIUM])]
    #[Versioned]
    private TaskPriority $priority;

    #[Column(type: "string", enumType: TaskStatus::class, options: ['default' => TaskStatus::PENDING])]
    #[Versioned]
    private TaskStatus $status;

    #[ManyToOne(targetEntity: Project::class, inversedBy: 'tasks')]
    #[JoinColumn(name: 'project', nullable: true)]
    #[Versioned]
    private ?Project $project = null;

    #[Column(type: 'datetime', nullable: true)]
    #[Versioned]
    private ?\DateTime $completionDate = null;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'assignedtasks')]
    #[JoinColumn(name: 'worker', nullable: false)]
    #[Versioned]
    private User $worker;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'createdtasks')]
    #[JoinColumn(name: 'creator', nullable: false)]
    #[Blameable(on: 'create')]
    #[Versioned]
    private User $creator;

    /**
     * @var Collection|Comment[]
     */
    #[OneToMany(targetEntity: Comment::class, mappedBy: 'task')]
    private Collection $comments;

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

    public function getPriority(): TaskPriority
    {
        return $this->priority;
    }

    public function setPriority(TaskPriority $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project = null): self
    {
        $this->project = $project;
        return $this;
    }

    public function getCompletionDate(): ?\DateTime
    {
        return $this->completionDate;
    }

    public function setCompletionDate(?\DateTime $date = new \DateTime()): self
    {
        $this->completionDate = $date;
        return $this;
    }

    public function getWorker(): User
    {
        return $this->worker;
    }

    public function setWorker(User $worker): self
    {
        $this->worker = $worker;
        return $this;
    }

    public function getCreator(): User
    {
        return $this->creator;
    }

    public function setCreator(User $creator): self
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
      return $this->comments;
    }

    /**
     * @param Comment $comment 
     */
    public function addComment(Comment $comment): self
    {
      $this->comments[] = $comment;
      return $this;
    }
}
