<?php

namespace App\Controller\Api\Project;

use App\Entity\Project;
use App\Response\ProjectResponse;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    #[Route('/project/{project}', name: 'api_project', requirements: ['project' => '%uuid_pattern%'], methods: ['GET'])]
    #[Tag(name: 'Projects')]
    public function __invoke(Project $project): Response
    {
        return $this->json(new ProjectResponse($project));
    }
}
