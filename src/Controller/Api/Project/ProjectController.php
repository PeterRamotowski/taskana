<?php

namespace App\Controller\Api\Project;

use App\Entity\Project;
use App\Response\ProjectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectController extends AbstractController
{
    #[Route('/project/{project}', name: 'api_project', requirements: ['project' => '%uuid_pattern%'], methods: ['GET'])]
    public function __invoke(Project $project): Response
    {
        return $this->json(new ProjectResponse($project));
    }
}
