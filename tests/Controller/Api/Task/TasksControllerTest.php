<?php

namespace App\Tests\Controller\Api\Task;

use App\Repository\TaskRepository;
use App\Tests\AppWebTestCase;

class TasksControllerTest extends AppWebTestCase
{
    public function testGetTasks(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $taskRepository = $container->get(TaskRepository::class);
        $tasks = $taskRepository->getList();
        if (empty($tasks)) {
            $this->markTestSkipped('No tasks found in the database.');
        }
        $client->request('GET', '/api/tasks');
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('title', $data[0]);
    }
}
