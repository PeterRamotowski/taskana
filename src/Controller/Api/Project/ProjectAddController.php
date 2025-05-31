<?php

namespace App\Controller\Api\Project;

use App\Data\ProjectAddData;
use App\Manager\ProjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class ProjectAddController extends AbstractController
{
    public function __construct(
        private readonly ProjectManager $projectManager,
    ) {
    }

    #[Route('/project', name: 'api_project_add', methods: ['POST'])]
    public function __invoke(
        #[MapRequestPayload] ProjectAddData $projectData,
    ): Response
    {
        $this->projectManager->createFromData($projectData);

        return new Response();
    }
}
