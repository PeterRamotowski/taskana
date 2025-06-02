<?php

namespace App\Tests\Controller\Api\Task;

use App\Data\TaskAddData;
use App\Entity\Enum\TaskPriority;
use App\Manager\TaskManager;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class TaskDeleteControllerTest extends AppWebTestCase
{
    public function testDeleteTask(): void
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

        $taskData = new TaskAddData();
        $taskData->title = 'Task To Delete';
        $taskData->description = 'To be deleted';
        $taskData->priority = TaskPriority::MEDIUM;
        $taskData->worker = $worker->getId();
        $taskData->project = $project->getId();

        /** @var TaskManager $taskManager */
        $taskManager = $container->get(TaskManager::class);
        $task = $taskManager->createFromData($taskData);
        $taskId = $task->getId();

        $entity = $taskRepository->find($taskId);
        $this->assertNotNull($entity);

        $client->request('DELETE', '/api/task/'.$taskId, [], [], ['CONTENT_TYPE' => 'application/json']);
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('""', $response->getContent());
        $deleted = $taskRepository->find($taskId);
        $this->assertNull($deleted);
    }
}
