<?php

namespace App\Controller\Api\User;

use App\Repository\UserRepository;
use App\Response\UserResponse;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UsersController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    #[Route('/users', name: 'api_users', methods: ['GET'])]
    #[Tag(name: 'Users')]
    public function __invoke(): Response
    {
        $usersList = $this->userRepository->getList();

        $users = [];

        foreach ($usersList as $user) {
            $users[] = new UserResponse($user);
        }

        return $this->json($users);
    }
}
