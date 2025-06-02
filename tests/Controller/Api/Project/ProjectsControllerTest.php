<?php

namespace App\Tests\Controller\Api\Project;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Tests\AppWebTestCase;

class ProjectsControllerTest extends AppWebTestCase
{
    public function testGetProjects(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        /** @var ProjectRepository $projectRepository */
        $projectRepository = $container->get(ProjectRepository::class);

        // Ensure at least one project exists
        $projects = $projectRepository->getList();
        if (empty($projects)) {
            $project = new Project();
            $project->setTitle('Test Project');
            $project->setDescription('Test Description');
            $em = $container->get('doctrine')->getManager();
            $em->persist($project);
            $em->flush();
        }

        $client->request('GET', '/api/projects');
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('title', $data[0]);
    }
}
