<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class IndexController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig
    ) {
    }

    #[Route('/', name: 'app_index')]
    #[Route('/app', name: 'app_app')]
    #[Route('/app/{slug}', name: 'app_pages')]
    #[Route('/app/{slug}/{param}', name: 'app_pages_')]
    #[Route('/app/{slug}/{param}/{sub}', name: 'app_pages__')]
    public function __invoke(): Response
    {
        return new Response(
            $this->twig->render('home.html.twig')
        );
    }
}
