<?php

namespace App\Controller\Api\Task;

use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Manager\TaskManager;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskStatusUpdateController extends AbstractController
{
    public function __construct(
        private readonly TaskManager $taskManager,
    ) {
    }

    #[Route('/task/{task}/status/{status}', name: 'api_task_status_update', requirements: ['task' => '%uuid_pattern%'], methods: ['PATCH'])]
    #[Tag(name: 'Tasks')]
    public function __invoke(Task $task, TaskStatus $status): Response
    {        
        $this->taskManager->updateStatus($task, $status);

        return new Response();
    }
}
