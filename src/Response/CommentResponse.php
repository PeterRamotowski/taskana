<?php

namespace App\Response;

use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;

class CommentResponse
{
    private readonly User $author;
    private readonly ?Task $task;

    public function __construct(
        private readonly Comment $comment
    ) {
        $this->author = $this->comment->getAuthor();
        $this->task = $this->comment->getTask();
    }

    public function getId(): Uuid
    {
        return $this->comment->getId();
    }

    public function getDescription(): ?string
    {
        return $this->comment->getDescription();
    }

    public function getAuthor(): string
    {
        return $this->author->getId();
    }

    public function getAuthorUsername(): string
    {
        return $this->author->getUsername();
    }

    public function getTask(): ?string
    {
        return $this->task?->getId();
    }

    public function getTaskTitle(): ?string
    {
        return $this->task?->getTitle();
    }

    public function getCreatedDate(): ?\DateTime
    {
        return $this->comment->getCreatedDate();
    }

    public function getUpdatedDate(): ?\DateTime
    {
        return $this->comment->getUpdatedDate();
    }
}
