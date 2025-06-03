<?php

namespace App\Tests\Entity;

use App\Entity\Factory\ProjectFactory;
use App\Entity\Factory\TaskFactory;
use App\Entity\Factory\UserFactory;
use App\Entity\Project;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class ProjectTest extends TestCase
{
    private Project $project;
    private string $testUuid;
    private string $testTitle;
    private string $testDescription;

    protected function setUp(): void
    {
        $this->testUuid = Uuid::v4()->toRfc4122();
        $this->testTitle = 'Test Project Title';
        $this->testDescription = 'Test Project Description';
        
        $this->project = new Project();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(Collection::class, $this->project->getTasks());
        $this->assertCount(0, $this->project->getTasks());
    }

    public function testIdGetterAndSetter(): void
    {
        $this->project->setId($this->testUuid);
        
        $this->assertInstanceOf(Uuid::class, $this->project->getId());
        $this->assertEquals($this->testUuid, $this->project->getId()->toRfc4122());
    }

    public function testTitleGetterAndSetter(): void
    {
        $this->project->setTitle($this->testTitle);
        
        $this->assertEquals($this->testTitle, $this->project->getTitle());
    }

    public function testDescriptionGetterAndSetter(): void
    {
        $this->project->setDescription($this->testDescription);
        
        $this->assertEquals($this->testDescription, $this->project->getDescription());
        
        $this->project->setDescription(null);
        
        $this->assertNull($this->project->getDescription());
    }

    public function testArchiveAndUnarchive(): void
    {
        $this->project->archive();
        
        $this->assertTrue($this->project->isArchived());
        
        $this->project->unarchive();
        
        $this->assertFalse($this->project->isArchived());
    }

    public function testAddTask(): void
    {
        $task = TaskFactory::create();
        
        $user = UserFactory::create();
        $task->setWorker($user);
        $task->setCreator($user);
        $task->setTitle('Test Task');
        
        $result = $this->project->addTask($task);
        
        $this->assertCount(1, $this->project->getTasks());
        $this->assertContains($task, $this->project->getTasks());
        
        $this->assertSame($this->project, $result);
    }

    public function testFactoryCreate(): void
    {
        $project = ProjectFactory::create();
        
        $this->assertInstanceOf(Project::class, $project);
        $this->assertInstanceOf(Uuid::class, $project->getId());
        $this->assertFalse($project->isArchived());
    }

    public function testTimestampableTrait(): void
    {
        $this->assertTrue(method_exists($this->project, 'getCreatedDate'));
        $this->assertTrue(method_exists($this->project, 'getUpdatedDate'));
    }

    public function testFluentInterface(): void
    {
        $result = $this->project
            ->setId($this->testUuid)
            ->setTitle($this->testTitle)
            ->setDescription($this->testDescription)
            ->archive();
        
        $this->assertSame($this->project, $result);
    }
}
