<?php

namespace App\Response;

use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Enum\TaskPriority;
use App\Entity\Enum\TaskStatus;
use Symfony\Component\Uid\Uuid;

class TaskResponse
{
    private readonly ?Project $project;
    private readonly User $worker;
    private readonly User $creator;

    public function __construct(
        private readonly Task $task,
    ) {
        $this->project = $this->task->getProject();
        $this->worker = $this->task->getWorker();
        $this->creator = $this->task->getCreator();
    }

    public function getId(): Uuid
    {
        return $this->task->getId();
    }

    public function getTitle(): string
    {
        return $this->task->getTitle();
    }

    public function getDescription(): ?string
    {
        return $this->task->getDescription();
    }

    public function getPriority(): TaskPriority
    {
        return $this->task->getPriority();
    }

    public function getStatus(): TaskStatus
    {
        return $this->task->getStatus();
    }

    public function getProject(): ?string
    {
        return $this->project?->getId();
    }

    public function getProjectTitle(): ?string
    {
        return $this->project?->getTitle();
    }

    public function getWorker(): string
    {
        return $this->worker->getId();
    }

    public function getWorkerUsername(): string
    {
        return $this->worker->getUsername();
    }

    public function getCreator(): string
    {
        return $this->creator->getId();
    }

    public function getCreatorUsername(): string
    {
        return $this->creator->getUsername();
    }

    public function getCreatedDate(): ?\DateTime
    {
        return $this->task->getCreatedDate();
    }

    public function getUpdatedDate(): ?\DateTime
    {
        return $this->task->getUpdatedDate();
    }

    public function getCompletionDate(): ?\DateTime
    {
        return $this->task->getCompletionDate();
    }

    public function getCommentsCount(): int
    {
        return $this->task->getComments()->count();
    }
}
