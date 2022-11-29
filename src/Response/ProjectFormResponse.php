<?php

namespace App\Response;

use App\Entity\Project;
use Symfony\Component\Uid\Uuid;

class ProjectFormResponse
{
    public function __construct(
        private readonly Project $project
    ) {
    }

    public function getValue(): Uuid
    {
        return $this->project->getId();
    }

    public function getTitle(): string
    {
        return $this->project->getTitle();
    }
}
