<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

class LoginController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly AuthenticationUtils $authenticationUtils
    ) {
    }

    #[Route('/login', name: 'user_login')]
    public function __invoke(): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $username = $this->authenticationUtils->getLastUsername();

        return new Response(
            $this->twig->render(
                'login.html.twig',
                [
                  'username' => $username,
                  'error' => $error,
                ]
            )
        );
    }
}
