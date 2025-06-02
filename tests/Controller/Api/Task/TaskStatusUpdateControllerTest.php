<?php

namespace App\Tests\Controller\Api\Task;

use App\Data\TaskAddData;
use App\Entity\Enum\TaskPriority;
use App\Entity\Enum\TaskStatus;
use App\Manager\TaskManager;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class TaskStatusUpdateControllerTest extends AppWebTestCase
{
    public function testUpdateTaskStatus(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $projectRepository = $container->get(ProjectRepository::class);
        $taskRepository = $container->get(TaskRepository::class);
        $taskManager = $container->get(TaskManager::class);

        $worker = $userRepository->findOneBy([]);
        $project = $projectRepository->findOneBy([]);
        $this->assertNotNull($worker);
        $this->assertNotNull($project);

        $taskData = new TaskAddData();
        $taskData->title = 'Task Status Test';
        $taskData->description = 'Status update test';
        $taskData->priority = TaskPriority::LOW;
        $taskData->worker = $worker->getId();
        $taskData->project = $project->getId();
        $task = $taskManager->createFromData($taskData);
        $taskId = $task->getId();

        // Update status to ACTIVE
        $client->request('PATCH', '/api/task/'.$taskId.'/status/active');
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('', $response->getContent());
        $updated = $taskRepository->find($taskId);
        $this->assertEquals(TaskStatus::ACTIVE, $updated->getStatus());

        // Update status to COMPLETE
        $client->request('PATCH', '/api/task/'.$taskId.'/status/complete');
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('', $response->getContent());
        $updated = $taskRepository->find($taskId);
        $this->assertEquals(TaskStatus::COMPLETE, $updated->getStatus());
    }
}
