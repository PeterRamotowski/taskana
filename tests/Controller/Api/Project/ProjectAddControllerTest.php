<?php

namespace App\Tests\Controller\Api\Project;

use App\Tests\AppWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProjectAddControllerTest extends AppWebTestCase
{
    public function testAddProject(): void
    {
        $client = $this->authorize();

        $projectData = [
            'title' => 'Test Project',
            'description' => 'Test Description',
        ];
        $client->request('POST', '/api/project', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($projectData));

        $response = $client->getResponse();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}