<?php

namespace App\EventListener;

use App\Entity\Project;
use Doctrine\ORM\Event\PreRemoveEventArgs;

class ProjectListener
{
    public function preRemove(Project $project, PreRemoveEventArgs $args): void
    {
        $tasks = $project->getTasks();

        foreach ($tasks as $task) {
            $task->setProject();
        }
    }
}
