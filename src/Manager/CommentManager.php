<?php

namespace App\Manager;

use App\Data\CommentAddData;
use App\Entity\Comment;
use App\Entity\Factory\CommentFactory;
use App\Manager\AppEntityManager;
use App\Repository\TaskRepository;

class CommentManager
{
    public function __construct(
        private readonly AppEntityManager $aem,
        private readonly TaskRepository $taskRepository,
    ) {
    }

    public function createFromData(
        CommentAddData $data,
        bool $save = true,
    ): Comment
    {
        $comment = CommentFactory::create();
        $this->buildFromData($comment, $data);

        if ($save) {
            $this->aem->save($comment);
        }

        return $comment;
    }

    private function buildFromData(Comment $comment, CommentAddData $data): void
    {
        $comment
            ->setDescription($data->description);

        if ($data->task) {
            $task = $this->taskRepository->getReference($data->task);
            $comment->setTask($task);
        }
    }
}
