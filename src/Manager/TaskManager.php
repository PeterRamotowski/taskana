<?php

namespace App\Manager;

use App\Data\TaskAddData;
use App\Data\TaskUpdateData;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Enum\TaskStatus;
use App\Entity\Factory\TaskFactory;
use App\Manager\AppEntityManager;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;

class TaskManager
{
    public function __construct(
        private readonly AppEntityManager $aem,
        private readonly ProjectRepository $projectRepository,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function createFromData(
        TaskAddData $data,
        bool $save = true,
    ): Task
    {
        $task = TaskFactory::create();
        $this->buildFromData($task, $data);

        if ($save) {
            $this->aem->save($task);
        }

        return $task;
    }

    public function updateFromData(Task $task, TaskUpdateData $data): Task
    {
        $this->buildFromData($task, $data);
        $this->aem->flush();
        return $task;
    }

    public function buildFromData(Task $task, TaskAddData|TaskUpdateData $data): void
    {
        /** @var User $worker */
        $worker = $this->userRepository->getReference($data->worker);

        $task
            ->setTitle($data->title)
            ->setDescription($data->description)
            ->setPriority($data->priority)
            ->setWorker($worker);

        if ($data->project) {
            $project = $this->projectRepository->getReference($data->project);
            $task->setProject($project);
        }
    }

    public function updateStatus(Task $task, TaskStatus $status): Task
    {
        $task->setStatus($status);

        if ($status == TaskStatus::COMPLETE) {
            $task->setCompletionDate();
        }

        $this->aem->flush();
        return $task;
    }

}
