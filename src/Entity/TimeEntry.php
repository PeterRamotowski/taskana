<?php

namespace App\Entity;

use App\Entity\Trait\EntityTimestampableTrait;
use App\Repository\TimeEntryRepository;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Blameable;
use Symfony\Component\Uid\Uuid;

#[Entity(repositoryClass: TimeEntryRepository::class)]
#[Table(name: 'time_entries')]
class TimeEntry implements EntityInterface
{
    use EntityTimestampableTrait;

    #[Id]
    #[Column(type: 'uuid', unique: true)]
    private string $id;

    #[ManyToOne(targetEntity: Task::class, inversedBy: 'timeEntries')]
    #[JoinColumn(name: 'task_id', nullable: false, onDelete: 'CASCADE')]
    private Task $task;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false)]
    #[Blameable(on: 'create')]
    private User $user;

    #[Column(type: 'datetime', nullable: false)]
    private \DateTime $startTime;

    #[Column(type: 'datetime', nullable: true)]
    private ?\DateTime $endTime = null;

    #[Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $duration = 0; // Duration in seconds

    #[Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->startTime = new \DateTime();
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

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): self
    {
        $this->task = $task;
        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTime $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTime $endTime): self
    {
        $this->endTime = $endTime;

        if ($endTime !== null) {
            $this->duration = $endTime->getTimestamp() - $this->startTime->getTimestamp();
        }

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Get duration in hours (decimal)
     */
    public function getDurationInHours(): float
    {
        return round($this->duration / 3600, 2);
    }

    /**
     * Check if time entry is currently running
     */
    public function isRunning(): bool
    {
        return $this->endTime === null;
    }

    /**
     * Stop the timer and calculate duration
     */
    public function stop(): self
    {
        if ($this->isRunning()) {
            $this->setEndTime(new \DateTime());
        }
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
}
