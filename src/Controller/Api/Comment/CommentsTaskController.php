<?php

namespace App\Controller\Api\Comment;

use App\Entity\Task;
use App\Repository\CommentRepository;
use App\Response\CommentResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentsTaskController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository
    ) {
    }

    #[Route('/comments/task/{task}', name: 'api_task_comments', requirements: ['task' => '%uuid_pattern%'], methods: ['GET'])]
    public function __invoke(Task $task): Response
    {
        $commentsList = $this->commentRepository->getTaskComments($task);

        $comments = [];

        foreach ($commentsList as $comment) {
            $comments[] = new CommentResponse($comment);
        }

        return $this->json($comments);
    }
}
