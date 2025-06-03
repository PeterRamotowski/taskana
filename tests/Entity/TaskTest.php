<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Enum\TaskPriority;
use App\Entity\Enum\TaskStatus;
use App\Entity\Factory\TaskFactory;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TaskTest extends TestCase
{
    private Task $task;
    private string $testUuid;
    private string $testTitle;
    private string $testDescription;

    protected function setUp(): void
    {
        $this->testUuid = Uuid::v4()->toRfc4122();
        $this->testTitle = 'Test Task Title';
        $this->testDescription = 'Test Task Description';
        $this->task = new Task();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(Collection::class, $this->task->getComments());
        $this->assertCount(0, $this->task->getComments());
    }

    public function testIdGetterAndSetter(): void
    {
        $this->task->setId($this->testUuid);
        $this->assertInstanceOf(Uuid::class, $this->task->getId());
        $this->assertEquals($this->testUuid, $this->task->getId()->toRfc4122());
    }

    public function testTitleGetterAndSetter(): void
    {
        $this->task->setTitle($this->testTitle);
        $this->assertEquals($this->testTitle, $this->task->getTitle());
    }

    public function testDescriptionGetterAndSetter(): void
    {
        $this->task->setDescription($this->testDescription);
        $this->assertEquals($this->testDescription, $this->task->getDescription());
        $this->task->setDescription(null);
        $this->assertNull($this->task->getDescription());
    }

    public function testPriorityGetterAndSetter(): void
    {
        $priority = TaskPriority::HIGH;
        $this->task->setPriority($priority);
        $this->assertEquals($priority, $this->task->getPriority());
    }

    public function testStatusGetterAndSetter(): void
    {
        $status = TaskStatus::ACTIVE;
        $this->task->setStatus($status);
        $this->assertEquals($status, $this->task->getStatus());
    }

    public function testProjectGetterAndSetter(): void
    {
        $project = new Project();
        $this->task->setProject($project);
        $this->assertSame($project, $this->task->getProject());
        $this->task->setProject(null);
        $this->assertNull($this->task->getProject());
    }

    public function testCompletionDateGetterAndSetter(): void
    {
        $date = new \DateTime('+1 day');
        $this->task->setCompletionDate($date);
        $this->assertEquals($date, $this->task->getCompletionDate());
        $this->task->setCompletionDate(null);
        $this->assertNull($this->task->getCompletionDate());
    }

    public function testWorkerGetterAndSetter(): void
    {
        $worker = new User();
        $this->task->setWorker($worker);
        $this->assertSame($worker, $this->task->getWorker());
    }

    public function testCreatorGetterAndSetter(): void
    {
        $creator = new User();
        $this->task->setCreator($creator);
        $this->assertSame($creator, $this->task->getCreator());
    }

    public function testAddComment(): void
    {
        $comment = new Comment();
        $result = $this->task->addComment($comment);
        $this->assertCount(1, $this->task->getComments());
        $this->assertContains($comment, $this->task->getComments());
        $this->assertSame($this->task, $result);
    }

    public function testFactoryCreate(): void
    {
        $task = TaskFactory::create();
        $this->assertInstanceOf(Task::class, $task);
        $this->assertInstanceOf(Uuid::class, $task->getId());
        $this->assertEquals(TaskStatus::PENDING, $task->getStatus());
    }

    public function testTimestampableTrait(): void
    {
        $this->assertTrue(method_exists($this->task, 'getCreatedDate'));
        $this->assertTrue(method_exists($this->task, 'getUpdatedDate'));
    }

    public function testFluentInterface(): void
    {
        $worker = new User();
        $creator = new User();
        $project = new Project();
        $date = new \DateTime();
        $result = $this->task
            ->setId($this->testUuid)
            ->setTitle($this->testTitle)
            ->setDescription($this->testDescription)
            ->setPriority(TaskPriority::LOW)
            ->setStatus(TaskStatus::PENDING)
            ->setWorker($worker)
            ->setCreator($creator)
            ->setProject($project)
            ->setCompletionDate($date);
        $this->assertSame($this->task, $result);
    }

    public function testTaskProperties(): void
    {
        $task = new Task();
        $id = Uuid::v4();
        $title = 'Test Task';
        $description = 'Test Description';
        $priority = TaskPriority::HIGH;
        $status = TaskStatus::ACTIVE;
        $worker = new User();
        $creator = new User();
        $project = new Project();
        $completionDate = new \DateTime('+1 day');

        $task->setId($id);
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setPriority($priority);
        $task->setStatus($status);
        $task->setWorker($worker);
        $task->setCreator($creator);
        $task->setProject($project);
        $task->setCompletionDate($completionDate);

        $this->assertEquals($id, $task->getId());
        $this->assertEquals($title, $task->getTitle());
        $this->assertEquals($description, $task->getDescription());
        $this->assertEquals($priority, $task->getPriority());
        $this->assertEquals($status, $task->getStatus());
        $this->assertSame($worker, $task->getWorker());
        $this->assertSame($creator, $task->getCreator());
        $this->assertSame($project, $task->getProject());
        $this->assertEquals($completionDate, $task->getCompletionDate());
    }

    public function testTaskDefaultValues(): void
    {
        $task = new Task();
        $this->assertNull($task->getDescription());
        $this->assertNull($task->getProject());
        $this->assertNull($task->getCompletionDate());
    }
}
