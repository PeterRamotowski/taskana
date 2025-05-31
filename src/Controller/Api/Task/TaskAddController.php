<?php

namespace App\Controller\Api\Task;

use App\Data\TaskAddData;
use App\Manager\TaskManager;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class TaskAddController extends AbstractController
{
    public function __construct(
        private readonly TaskManager $taskManager,
    ) {
    }

    #[Route('/task', name: 'api_task_add', methods: ['POST'])]
    #[Tag(name: 'Tasks')]
    public function __invoke(
        #[MapRequestPayload] TaskAddData $taskData,
    ): Response
    {
        $this->taskManager->createFromData($taskData);
        
        return new Response();
    }
}
