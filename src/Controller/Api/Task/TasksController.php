<?php

namespace App\Controller\Api\Task;

use App\Repository\TaskRepository;
use App\Response\TaskResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository
    ) {
    }

    #[Route('/tasks', name: 'api_tasks', methods: ['GET'])]
    public function __invoke(): Response
    {
        $tasksList = $this->taskRepository->getList();

        $tasks = [];

        foreach ($tasksList as $task) {
            $tasks[] = new TaskResponse($task);
        }

        return $this->json($tasks);
    }
}
