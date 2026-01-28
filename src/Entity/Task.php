<?php

namespace App\Entity;

use App\Entity\Enum\RecurrencePattern;
use App\Entity\Enum\TaskPriority;
use App\Entity\Enum\TaskStatus;
use App\Entity\Trait\EntityTimestampableTrait;
use App\Entity\User;
use App\EventListener\TaskListener;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @var Collection|TimeEntry[]
     */
    #[OneToMany(targetEntity: TimeEntry::class, mappedBy: 'task', cascade: ['persist', 'remove'])]
    private Collection $timeEntries;

    #[Column(type: 'float', nullable: true)]
    #[Versioned]
    private ?float $estimatedHours = null;

    #[Column(type: 'datetime', nullable: true)]
    #[Versioned]
    private ?\DateTime $dueDate = null;

    #[Column(type: 'boolean', options: ['default' => false])]
    #[Versioned]
    private bool $isRecurring = false;

    #[Column(type: "string", enumType: RecurrencePattern::class, nullable: true)]
    #[Versioned]
    private ?RecurrencePattern $recurrencePattern = null;

    #[Column(type: 'integer', nullable: true, options: ['default' => 1])]
    #[Versioned]
    private ?int $recurrenceInterval = 1; // e.g., every 2 weeks = interval: 2, pattern: WEEKLY

    #[Column(type: 'datetime', nullable: true)]
    #[Versioned]
    private ?\DateTime $recurrenceEndDate = null;

    #[ManyToOne(targetEntity: Task::class, inversedBy: 'recurringInstances')]
    #[JoinColumn(name: 'parent_task_id', nullable: true, onDelete: 'SET NULL')]
    #[Versioned]
    private ?Task $parentTask = null;

    /**
     * @var Collection|Task[]
     */
    #[OneToMany(targetEntity: Task::class, mappedBy: 'parentTask')]
    private Collection $recurringInstances;

    #[Column(type: 'datetime', nullable: true)]
    #[Versioned]
    private ?\DateTime $lastRecurrenceGeneration = null;

    public function __construct() {
        $this->comments = new ArrayCollection();
        $this->timeEntries = new ArrayCollection();
        $this->recurringInstances = new ArrayCollection();
    }

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

    /**
     * @return Collection|TimeEntry[]
     */
    public function getTimeEntries(): Collection
    {
        return $this->timeEntries;
    }

    public function addTimeEntry(TimeEntry $timeEntry): self
    {
        if (!$this->timeEntries->contains($timeEntry)) {
            $this->timeEntries[] = $timeEntry;
            $timeEntry->setTask($this);
        }
        return $this;
    }

    /**
     * Get total tracked time in hours
     */
    public function getTotalTrackedHours(): float
    {
        $totalSeconds = 0;
        foreach ($this->timeEntries as $entry) {
            if (!$entry->isRunning()) {
                $totalSeconds += $entry->getDuration();
            }
        }
        return round($totalSeconds / 3600, 2);
    }

    /**
     * Get currently running time entry
     */
    public function getActiveTimeEntry(): ?TimeEntry
    {
        foreach ($this->timeEntries as $entry) {
            if ($entry->isRunning()) {
                return $entry;
            }
        }
        return null;
    }

    public function getEstimatedHours(): ?float
    {
        return $this->estimatedHours;
    }

    public function setEstimatedHours(?float $estimatedHours): self
    {
        $this->estimatedHours = $estimatedHours;
        return $this;
    }

    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTime $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function isOverdue(): bool
    {
        if ($this->dueDate === null || $this->status === TaskStatus::COMPLETE) {
            return false;
        }
        return $this->dueDate < new \DateTime();
    }

    public function getIsRecurring(): bool
    {
        return $this->isRecurring;
    }

    public function setIsRecurring(bool $isRecurring): self
    {
        $this->isRecurring = $isRecurring;
        return $this;
    }

    public function getRecurrencePattern(): ?RecurrencePattern
    {
        return $this->recurrencePattern;
    }

    public function setRecurrencePattern(?RecurrencePattern $recurrencePattern): self
    {
        $this->recurrencePattern = $recurrencePattern;
        return $this;
    }

    public function getRecurrenceInterval(): ?int
    {
        return $this->recurrenceInterval;
    }

    public function setRecurrenceInterval(?int $recurrenceInterval): self
    {
        $this->recurrenceInterval = $recurrenceInterval;
        return $this;
    }

    public function getRecurrenceEndDate(): ?\DateTime
    {
        return $this->recurrenceEndDate;
    }

    public function setRecurrenceEndDate(?\DateTime $recurrenceEndDate): self
    {
        $this->recurrenceEndDate = $recurrenceEndDate;
        return $this;
    }

    public function getParentTask(): ?Task
    {
        return $this->parentTask;
    }

    public function setParentTask(?Task $parentTask): self
    {
        $this->parentTask = $parentTask;
        return $this;
    }

    /**
     * @return Collection|Task[]
     */
    public function getRecurringInstances(): Collection
    {
        return $this->recurringInstances;
    }

    public function getLastRecurrenceGeneration(): ?\DateTime
    {
        return $this->lastRecurrenceGeneration;
    }

    public function setLastRecurrenceGeneration(?\DateTime $lastRecurrenceGeneration): self
    {
        $this->lastRecurrenceGeneration = $lastRecurrenceGeneration;
        return $this;
    }

    /**
     * Calculate the next occurrence date based on recurrence settings
     */
    public function getNextOccurrenceDate(?\DateTime $fromDate = null): ?\DateTime
    {
        if (!$this->isRecurring || $this->recurrencePattern === null) {
            return null;
        }

        $baseDate = $fromDate ?? ($this->dueDate ?? new \DateTime());
        $nextDate = clone $baseDate;
        $interval = $this->recurrenceInterval ?? 1;

        switch ($this->recurrencePattern) {
            case RecurrencePattern::DAILY:
                $nextDate->modify("+{$interval} days");
                break;
            case RecurrencePattern::WEEKLY:
                $nextDate->modify("+{$interval} weeks");
                break;
            case RecurrencePattern::MONTHLY:
                $nextDate->modify("+{$interval} months");
                break;
            case RecurrencePattern::YEARLY:
                $nextDate->modify("+{$interval} years");
                break;
        }

        if ($this->recurrenceEndDate !== null && $nextDate > $this->recurrenceEndDate) {
            return null;
        }

        return $nextDate;
    }
}
