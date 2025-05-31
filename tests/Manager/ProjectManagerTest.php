<?php

namespace App\Tests\Manager;

use App\Data\ProjectAddData;
use App\Data\ProjectUpdateData;
use App\Entity\Factory\ProjectFactory;
use App\Entity\Project;
use App\Manager\AppEntityManager;
use App\Manager\ProjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ProjectManagerTest extends TestCase
{
    private ProjectManager $projectManager;
    private AppEntityManager&MockObject $aem;

    protected function setUp(): void
    {
        $this->aem = $this->createMock(AppEntityManager::class);
        $this->projectManager = new ProjectManager($this->aem);
    }

    public function testCreateFromData(): void
    {
        $data = new ProjectAddData();
        $data->title = 'Test Project';
        $data->description = 'Test Description';

        $this->aem->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Project::class));

        $project = $this->projectManager->createFromData($data);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals('Test Project', $project->getTitle());
        $this->assertEquals('Test Description', $project->getDescription());
    }

    public function testUpdateFromData(): void
    {
        $project = ProjectFactory::create();
        $data = new ProjectUpdateData();
        $data->title = 'Updated Project';
        $data->description = 'Updated Description';

        $updatedProject = $this->projectManager->updateFromData($project, $data);

        $this->assertInstanceOf(Project::class, $updatedProject);
        $this->assertEquals('Updated Project', $updatedProject->getTitle());
        $this->assertEquals('Updated Description', $updatedProject->getDescription());
    }
}