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

    public function createFromData(TaskAddData $data): void
    {
        $task = TaskFactory::create();
        $this->buildFromData($task, $data);
        $this->aem->save($task);
    }

    public function updateFromData(Task $task, TaskUpdateData $data): void
    {
        $this->buildFromData($task, $data);
        $this->aem->save($task);
    }

    private function buildFromData(Task $task, TaskAddData|TaskUpdateData $data): void
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

    public function updateStatus(Task $task, TaskStatus $status): void
    {
        $task->setStatus($status);

        if ($status == TaskStatus::COMPLETE) {
            $task->setCompletionDate();
        }

        $this->aem->save($task);
    }

}
