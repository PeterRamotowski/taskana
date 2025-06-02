<?php

namespace App\Tests\Controller\Api\Project;

use App\Data\ProjectAddData;
use App\Manager\ProjectManager;
use App\Repository\ProjectRepository;
use App\Tests\AppWebTestCase;

class ProjectUpdateControllerTest extends AppWebTestCase
{
    public function testUpdateProject(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $projectManager = $container->get(ProjectManager::class);
        $projectRepository = $container->get(ProjectRepository::class);

        // Create a project to update
        $data = new ProjectAddData();
        $data->title = 'Original Title';
        $data->description = 'Original Description';
        $project = $projectManager->createFromData($data);
        $projectId = $project->getId();

        // Prepare update data
        $updateData = [
            'id' => (string)$projectId,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ];

        $client->request(
            'PUT',
            '/api/project/'.$projectId,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updateData)
        );

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertEquals('', $response->getContent());

        // Verify the project was updated
        $updated = $projectRepository->find($projectId);
        $this->assertEquals('Updated Title', $updated->getTitle());
        $this->assertEquals('Updated Description', $updated->getDescription());
    }
}
