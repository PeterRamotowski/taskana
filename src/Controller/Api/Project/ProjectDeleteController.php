<?php

namespace App\Controller\Api\Project;

use App\Entity\Project;
use App\Manager\AppEntityManager;
use OpenApi\Attributes\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProjectDeleteController extends AbstractController
{
    public function __construct(
        private readonly AppEntityManager $aem,
    ) {
    }

    #[Route('/project/{project}', name: 'api_project_delete', requirements: ['project' => '%uuid_pattern%'], methods: ['DELETE'])]
    #[Tag(name: 'Projects')]
    public function __invoke(Project $project): Response
    {
        $this->aem->remove($project);

        return $this->json('');
    }
}
