<?php

namespace App\Controller\Api\Task;

use App\Entity\Task;
use App\Manager\AppEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskDeleteController extends AbstractController
{
    public function __construct(
        private readonly AppEntityManager $aem,
    ) {
    }

    #[Route('/task/{task}', name: 'api_task_delete', requirements: ['task' => '%uuid_pattern%'], methods: ['DELETE'])]
    public function __invoke(Task $task): Response
    {
        $this->aem->remove($task);

        return $this->json('');
    }
}
