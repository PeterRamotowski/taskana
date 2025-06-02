<?php

namespace App\Tests\Controller\Api\Task;

use App\Repository\TaskRepository;
use App\Tests\AppWebTestCase;

class TaskControllerTest extends AppWebTestCase
{
    public function testGetTask(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $taskRepository = $container->get(TaskRepository::class);
        $task = $taskRepository->findOneBy([]);
        $this->assertNotNull($task, 'No task found in the database.');
        $taskId = $task->getId();
        $client->request('GET', '/api/task/'.$taskId);
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals((string)$taskId, $data['id']);
        $this->assertEquals($task->getTitle(), $data['title']);
    }
}
