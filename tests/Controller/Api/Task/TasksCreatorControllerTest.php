<?php

namespace App\Tests\Controller\Api\Task;

use App\Data\TaskAddData;
use App\Entity\Enum\TaskPriority;
use App\Manager\TaskManager;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class TasksCreatorControllerTest extends AppWebTestCase
{
    public function testGetTasksForCreator(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $taskRepository = $container->get(TaskRepository::class);
        $projectRepository = $container->get(ProjectRepository::class);
        $taskManager = $container->get(TaskManager::class);

        $creator = $userRepository->findOneBy([]);
        $this->assertNotNull($creator);

        // Ensure at least one task is created by this creator
        $tasks = $taskRepository->getCreatedTasks($creator);
        if (empty($tasks)) {
            $project = $projectRepository->findOneBy([]);
            $worker = $userRepository->findOneBy(['id' => ['neq' => $creator->getId()]]);
            if (!$worker || $worker->getId() === $creator->getId()) {
                $users = $userRepository->findAll();
                foreach ($users as $user) {
                    if ($user->getId() !== $creator->getId()) {
                        $worker = $user;
                        break;
                    }
                }
            }
            $this->assertNotNull($project);
            $this->assertNotNull($worker);
            $taskData = new TaskAddData();
            $taskData->title = 'Creator Test Task';
            $taskData->description = 'Created by creator for test';
            $taskData->priority = TaskPriority::MEDIUM;
            $taskData->worker = $worker->getId();
            $taskData->project = $project->getId();
            $task = $taskManager->createFromData($taskData);
            $task->setCreator($creator);
            $container->get('doctrine')->getManager()->flush();
        }

        $client->request('GET', '/api/tasks/creator/'.$creator->getId());
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('title', $data[0]);
    }
}
