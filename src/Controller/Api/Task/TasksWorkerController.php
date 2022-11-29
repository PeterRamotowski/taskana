<?php

namespace App\Controller\Api\Task;

use App\Entity\User;
use App\Repository\TaskRepository;
use App\Response\TaskResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksWorkerController extends AbstractController
{
    public function __construct(
        private readonly TaskRepository $taskRepository,
    ) {
    }

    #[Route('/tasks/worker/{id}', name: 'api_tasks_worker', requirements: ['id' => '%uuid_pattern%'], methods: ['GET'])]
    #[ParamConverter('worker', options: ['mapping' => ['id' => 'id']])]
    public function __invoke(User $worker): Response
    {
        $tasksList = $this->taskRepository->getAssignedTasks($worker);

        $tasks = [];

        foreach ($tasksList as $task) {
            $tasks[] = new TaskResponse($task);
        }

        return $this->json($tasks);
    }
}
