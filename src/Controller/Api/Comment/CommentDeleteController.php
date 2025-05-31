<?php

namespace App\Controller\Api\Comment;

use App\Entity\Comment;
use App\Manager\AppEntityManager;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentDeleteController extends AbstractController
{
    public function __construct(
        private readonly AppEntityManager $aem,
    ) {
    }

    #[Route('/comment/{comment}', name: 'api_comment_delete', requirements: ['comment' => '%uuid_pattern%'], methods: ['DELETE'])]
    #[Tag(name: 'Comments')]
    public function __invoke(Comment $comment): Response
    {
        $this->aem->remove($comment);

        return $this->json('');
    }
}
