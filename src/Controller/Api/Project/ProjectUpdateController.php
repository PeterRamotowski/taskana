<?php

namespace App\Controller\Api\Project;

use App\ArgumentResolver\RequestBody;
use App\Data\ProjectUpdateData;
use App\Entity\Project;
use App\Manager\ProjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectUpdateController extends AbstractController
{
    public function __construct(
        private readonly ProjectManager $projectManager,
    ) {
    }

    #[Route('/project/{project}', name: 'api_project_update', requirements: ['project' => '%uuid_pattern%'], methods: ['PUT'])]
    public function __invoke(Project $project, #[RequestBody] ProjectUpdateData $projectData): Response
    {
        $this->projectManager->updateFromData($project, $projectData);

        return new Response();
    }
}
