<?php

namespace App\Tests\Controller\Api\Comment;

use App\Data\CommentAddData;
use App\Manager\CommentManager;
use App\Repository\CommentRepository;
use App\Repository\TaskRepository;
use App\Tests\AppWebTestCase;

class CommentsTaskControllerTest extends AppWebTestCase
{
    public function testGetCommentsForTask(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $taskRepository = $container->get(TaskRepository::class);
        $commentRepository = $container->get(CommentRepository::class);
        $task = $taskRepository->findOneBy([]);
        $this->assertNotNull($task);
        // Ensure at least one comment exists for this task
        $comments = $commentRepository->getTaskComments($task);
        if (empty($comments)) {
            $commentData = new CommentAddData();
            $commentData->description = 'Test comment for task';
            $commentData->task = $task->getId();
            $commentManager = $container->get(CommentManager::class);
            $commentManager->createFromData($commentData);
        }
        $client->request('GET', '/api/comments/task/'.$task->getId());
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('description', $data[0]);
    }
}
