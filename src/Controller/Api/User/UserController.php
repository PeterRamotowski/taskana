<?php

namespace App\Controller\Api\User;

use App\Entity\User;
use App\Response\UserResponse;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user/{user}', name: 'api_user', requirements: ['user' => '%uuid_pattern%'], methods: ['GET'])]
    #[Tag(name: 'Users')]
    public function __invoke(User $user): Response
    {
        return $this->json(new UserResponse($user));
    }
}
