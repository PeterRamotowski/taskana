<?php

namespace App\Controller\Api\Task;

use App\Entity\User;
use App\Repository\TaskRepository;
use App\Response\TaskResponse;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksCreatorController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
    ) {
    }

    #[Route('/tasks/creator/{creator}', name: 'api_tasks_creator', requirements: ['creator' => '%uuid_pattern%'], methods: ['GET'])]
    #[Tag(name: 'Tasks')]
    public function __invoke(User $creator): Response
    {
        $tasksList = $this->taskRepository->getCreatedTasks($creator);

        $tasks = [];

        foreach ($tasksList as $task) {
            $tasks[] = new TaskResponse($task);
        }

        return $this->json($tasks);
    }
}
