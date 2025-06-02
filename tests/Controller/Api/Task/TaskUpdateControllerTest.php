<?php

namespace App\Tests\Controller\Api\Task;

use App\Data\TaskAddData;
use App\Entity\Enum\TaskPriority;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Repository\ProjectRepository;
use App\Tests\AppWebTestCase;

class TaskUpdateControllerTest extends AppWebTestCase
{
    public function testUpdateTask(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $projectRepository = $container->get(ProjectRepository::class);
        $taskRepository = $container->get(TaskRepository::class);

        $worker = $userRepository->findOneBy([]);
        $project = $projectRepository->findOneBy([]);
        $this->assertNotNull($worker);
        $this->assertNotNull($project);

        // Add a Task using TaskAddData
        $taskData = new TaskAddData();
        $taskData->title = 'Task To Update';
        $taskData->description = 'Old Description';
        $taskData->priority = TaskPriority::LOW;
        $taskData->worker = $worker->getId();
        $taskData->project = $project->getId();
        $taskManager = $container->get(\App\Manager\TaskManager::class);
        $task = $taskManager->createFromData($taskData);
        $taskId = $task->getId();

        $updateData = [
            'id' => (string)$taskId,
            'title' => 'Updated Task',
            'description' => 'Updated Description',
            'priority' => TaskPriority::HIGH->value,
            'project' => (string)$project->getId(),
            'worker' => (string)$worker->getId(),
        ];

        $client->request(
            'PUT',
            '/api/task/'.$taskId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('', $response->getContent());
        $updated = $taskRepository->find($taskId);
        $this->assertEquals('Updated Task', $updated->getTitle());
        $this->assertEquals('Updated Description', $updated->getDescription());
        $this->assertEquals(TaskPriority::HIGH, $updated->getPriority());
    }
}
