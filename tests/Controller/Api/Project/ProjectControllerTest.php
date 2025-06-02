<?php

namespace App\Tests\Controller\Api\Project;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Tests\AppWebTestCase;

class ProjectControllerTest extends AppWebTestCase
{
    public function testGetProject(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();

        /** @var ProjectRepository $projectRepository */
        $projectRepository = $container->get(ProjectRepository::class);

        /** @var Project $project */
        $project = $projectRepository->getList()[0] ?? null;
        $this->assertNotNull($project, 'No project found in the database.');

        $projectId = $project->getId();
        $client->request('GET', '/api/project/'.$projectId);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($projectId, $data['id']);
        $this->assertEquals($project->getTitle(), $data['title']);
    }
}
