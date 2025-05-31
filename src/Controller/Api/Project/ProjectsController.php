<?php

namespace App\Controller\Api\Project;

use App\Repository\ProjectRepository;
use App\Response\ProjectResponse;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ProjectsController extends AbstractController
{
    public function __construct(
        private readonly ProjectRepository $projectRepository
    ) {
    }

    #[Route('/projects', name: 'api_projects', methods: ['GET'])]
    #[Tag(name: 'Projects')]
    public function __invoke(): Response
    {
        $projectsList = $this->projectRepository->getList();

        $projects = [];

        foreach ($projectsList as $project) {
            $projects[] = new ProjectResponse($project);
        }

        return $this->json($projects);
    }
}
