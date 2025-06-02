<?php

namespace App\Tests\Controller\Api\Project;

use App\Data\ProjectAddData;
use App\Manager\ProjectManager;
use App\Repository\ProjectRepository;
use App\Tests\AppWebTestCase;

class ProjectDeleteControllerTest extends AppWebTestCase
{
    public function testDeleteProject(): void
    {
        $client = $this->authorize();

        $container = static::getContainer();
        $projectManager = $container->get(ProjectManager::class);
        $projectRepository = $container->get(ProjectRepository::class);

        $data = new ProjectAddData();
        $data->title = 'Test Project';
        $data->description = 'Test Description';
        $project = $projectManager->createFromData($data);
        $projectId = $project->getId();

        $entity = $projectRepository->find($projectId);
        $this->assertNotNull($entity);

        $client->request('DELETE', '/api/project/'.$projectId, [], [], ['CONTENT_TYPE' => 'application/json']);
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('""', $response->getContent());

        $deleted = $projectRepository->find($projectId);
        $this->assertNull($deleted);
    }
}
