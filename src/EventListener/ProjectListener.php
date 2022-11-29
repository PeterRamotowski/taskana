<?php

namespace App\EventListener;

use App\Entity\Project;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ProjectListener
{
    public function preRemove(Project $project, LifecycleEventArgs $args): void
    {
        $tasks = $project->getTasks();

        foreach ($tasks as $task) {
            $task->setProject();
        }
    }
}
