<?php

namespace App\Controller\Api\Comment;

use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use App\Data\CommentAddData;
use App\Manager\CommentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentAddController extends AbstractController
{
    public function __construct(
        private readonly CommentManager $commentManager,
    ) {
    }

    #[Route('/comment', name: 'api_comment_add', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] CommentAddData $commentData,
    ): Response
    {
        $this->commentManager->createFromData($commentData);

        return new Response();
    }
}
