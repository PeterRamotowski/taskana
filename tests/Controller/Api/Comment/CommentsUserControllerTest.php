<?php

namespace App\Tests\Controller\Api\Comment;

use App\Data\CommentAddData;
use App\Manager\CommentManager;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class CommentsUserControllerTest extends AppWebTestCase
{
    public function testGetCommentsForUser(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);
        $commentRepository = $container->get(CommentRepository::class);
        $user = $userRepository->findOneBy([]);
        $this->assertNotNull($user);
        // Ensure at least one comment exists for this user
        $comments = $commentRepository->getUserComments($user);
        if (empty($comments)) {
            $commentData = new CommentAddData();
            $commentData->description = 'Test comment for user';
            $commentData->task = null;
            $commentManager = $container->get(CommentManager::class);
            $comment = $commentManager->createFromData($commentData);
            $comment->setAuthor($user);
            $container->get('doctrine')->getManager()->flush();
        }
        $client->request('GET', '/api/comments/user/'.$user->getId());
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('description', $data[0]);
    }
}
