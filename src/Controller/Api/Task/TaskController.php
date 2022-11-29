<?php

namespace App\Controller\Api\Task;

use App\Entity\Task;
use App\Response\TaskResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[Route('/task/{task}', name: 'api_task', requirements: ['task' => '%uuid_pattern%'], methods: ['GET'])]
    public function __invoke(Task $task): Response
    {
        return $this->json(new TaskResponse($task));
    }
}
