<?php

namespace App\Tests\Controller\Api\Comment;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class CommentAddControllerTest extends AppWebTestCase
{
    public function testAddComment(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $taskRepository = $container->get(TaskRepository::class);
        $userRepository = $container->get(UserRepository::class);
        $task = $taskRepository->findOneBy([]);
        $user = $userRepository->findOneBy([]);
        $this->assertNotNull($task);
        $this->assertNotNull($user);
        $commentData = [
            'description' => 'API add comment',
            'task' => (string)$task->getId(),
            'author' => (string)$user->getId(),
        ];
        $client->request(
            'POST',
            '/api/comment',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($commentData)
        );
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('', $response->getContent());
    }
}
