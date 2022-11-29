<?php

namespace App\Controller\Api\Comment;

use App\Entity\User;
use App\Repository\CommentRepository;
use App\Response\CommentResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentsUserController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository
    ) {
    }

    #[Route('/comments/user/{user}', name: 'api_user_comments', requirements: ['user' => '%uuid_pattern%'], methods: ['GET'])]
    public function __invoke(User $user): Response
    {
        $commentsList = $this->commentRepository->getUserComments($user);

        $comments = [];

        foreach ($commentsList as $comment) {
            $comments[] = new CommentResponse($comment);
        }

        return $this->json($comments);
    }
}
