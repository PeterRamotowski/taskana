<?php

namespace App\EventListener;

use App\Entity\Task;
use App\Manager\AppEntityManager;
use Doctrine\ORM\Event\PreRemoveEventArgs;

class TaskListener
{
    public function __construct(
        private readonly AppEntityManager $aem,
    ) {
    }

    public function preRemove(Task $task, PreRemoveEventArgs $args): void
    {
        $comments = $task->getComments();

        foreach ($comments as $comment) {
            $this->aem->preRemove($comment);
        }
    }
}
