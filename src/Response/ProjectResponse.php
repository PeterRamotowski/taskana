<?php

namespace App\Response;

use App\Entity\Project;
use Symfony\Component\Uid\Uuid;

class ProjectResponse
{
    public function __construct(
        private readonly Project $project
    ) {
    }

    public function getId(): Uuid
    {
        return $this->project->getId();
    }

    public function getTitle(): string
    {
        return $this->project->getTitle();
    }

    public function getDescription(): ?string
    {
        return $this->project->getDescription();
    }

    public function getCreatedDate(): ?\DateTime
    {
        return $this->project->getCreatedDate();
    }

    public function getUpdatedDate(): ?\DateTime
    {
        return $this->project->getUpdatedDate();
    }

    public function getTasksCount(): int
    {
        return $this->project->getTasks()->count();
    }
}
