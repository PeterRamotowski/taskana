<?php

namespace App\Controller\Api\Task;

use App\ArgumentResolver\RequestBody;
use App\Data\TaskUpdateData;
use App\Entity\Task;
use App\Manager\TaskManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskUpdateController extends AbstractController
{
    public function __construct(
        private readonly TaskManager $taskManager,
    ) {
    }

    #[Route('/task/{task}', name: 'api_task_update', requirements: ['task' => '%uuid_pattern%'], methods: ['PUT'])]
    public function __invoke(Task $task, #[RequestBody] TaskUpdateData $taskData): Response
    {
        $this->taskManager->updateFromData($task, $taskData);

        return new Response();
    }
}
