<?php

namespace App\Tests\Controller\Api\Comment;

use App\Data\CommentAddData;
use App\Manager\CommentManager;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class CommentDeleteControllerTest extends AppWebTestCase
{
    public function testDeleteComment(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $commentRepository = $container->get(CommentRepository::class);
        $comment = $commentRepository->findOneBy([]);
        if (!$comment) {
            $commentManager = $container->get(CommentManager::class);
            $commentData = new CommentAddData();
            $commentData->description = 'Test comment for delete';
            $commentData->task = null;
            $comment = $commentManager->createFromData($commentData);

            $userRepository = $container->get(UserRepository::class);
            $user = $userRepository->findOneBy([]);
            $comment->setAuthor($user);
            $container->get('doctrine')->getManager()->flush();
        }
        $commentId = $comment->getId();
        $client->request('DELETE', '/api/comment/'.$commentId, [], [], ['CONTENT_TYPE' => 'application/json']);
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('""', $response->getContent());
        $deleted = $commentRepository->find($commentId);
        $this->assertNull($deleted);
    }
}
