<?php

namespace App\Controller\Api\User;

use App\Repository\UserRepository;
use App\Response\UserCurrentResponse;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserCurrentController extends AbstractController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserRepository $userRepository,
    ) {
    }

    #[Route('/user/current', name: 'user_account', methods: 'GET')]
    #[Tag(name: 'Users')]
    public function __invoke(): Response
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            return new Response();
        }

        $currentUser = $token->getUser();

        if (!$currentUser) {
            return new Response();
        }

        $user = $this->userRepository->loadUserByIdentifier($currentUser->getUserIdentifier());

        if (!$user) {
            return new Response();
        }

        $response = new UserCurrentResponse($user);
        return new JsonResponse($response->getData());
    }
}
