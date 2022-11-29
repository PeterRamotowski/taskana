<?php

namespace App\Response;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;

class UserResponse
{
    public function __construct(
        private readonly User $user
    ) {
    }

    public function getId(): Uuid
    {
        return $this->user->getId();
    }

    public function getName(): string
    {
        return $this->user->getName();
    }

    public function getEmail(): string
    {
        return $this->user->getEmail();
    }

    public function getAssignedTasksCount(): int
    {
        return $this->user->getAssignedTasks()->count();
    }

    public function getCreatedTasksCount(): int
    {
        return $this->user->getCreatedTasks()->count();
    }

    public function getCommentsCount(): int
    {
        return $this->user->getComments()->count();
    }
}
