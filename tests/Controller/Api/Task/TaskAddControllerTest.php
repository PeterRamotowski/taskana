<?php

namespace App\Tests\Controller\Api\Task;

use App\Entity\Enum\TaskPriority;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class TaskAddControllerTest extends AppWebTestCase
{
    public function testAddTask(): void
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

        $data = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'priority' => TaskPriority::MEDIUM->value,
            'project' => $project->getId(),
            'worker' => $worker->getId(),
        ];

        $client->request(
            'POST',
            '/api/task',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('', $response->getContent());

        $task = $taskRepository->findOneBy(['title' => 'Test Task']);
        $this->assertNotNull($task);
        $this->assertEquals('Test Task', $task->getTitle());
        $this->assertEquals('Test Description', $task->getDescription());
        $this->assertEquals(TaskPriority::MEDIUM, $task->getPriority());
        $this->assertEquals($worker->getId(), $task->getWorker()->getId());
        $this->assertEquals($project->getId(), $task->getProject()->getId());
    }
}
