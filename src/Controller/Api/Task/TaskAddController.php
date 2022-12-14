<?php

namespace App\Controller\Api\Task;

use App\ArgumentResolver\RequestBody;
use App\Data\TaskAddData;
use App\Manager\TaskManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskAddController extends AbstractController
{
    public function __construct(
        private readonly TaskManager $taskManager,
    ) {
    }

    #[Route('/task', name: 'api_task_add', methods: ['POST'])]
    public function __invoke(#[RequestBody] TaskAddData $taskData): Response
    {
        $this->taskManager->createFromData($taskData);
        
        return new Response();
    }
}
