<?php

namespace App\Tests\Controller\Api\Task;

use App\Data\TaskAddData;
use App\Entity\Enum\TaskPriority;
use App\Manager\TaskManager;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class TasksWorkerControllerTest extends AppWebTestCase
{
    public function testGetTasksForWorker(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $taskRepository = $container->get(TaskRepository::class);
        $projectRepository = $container->get(ProjectRepository::class);
        $taskManager = $container->get(TaskManager::class);

        $worker = $userRepository->findOneBy([]);
        $this->assertNotNull($worker);

        // Ensure at least one task is assigned to this worker
        $tasks = $taskRepository->getAssignedTasks($worker);
        if (empty($tasks)) {
            $project = $projectRepository->findOneBy([]);
            $this->assertNotNull($project);
            $taskData = new TaskAddData();
            $taskData->title = 'Worker Test Task';
            $taskData->description = 'Assigned to worker for test';
            $taskData->priority = TaskPriority::MEDIUM;
            $taskData->worker = $worker->getId();
            $taskData->project = $project->getId();
            $taskManager->createFromData($taskData);
        }

        $client->request('GET', '/api/tasks/worker/'.$worker->getId());
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('title', $data[0]);
    }
}
