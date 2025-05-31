<?php

namespace App\Tests\Manager;

use App\Data\TaskAddData;
use App\Data\TaskUpdateData;
use App\Entity\Enum\TaskPriority;
use App\Entity\Enum\TaskStatus;
use App\Entity\Factory\TaskFactory;
use App\Entity\Task;
use App\Entity\User;
use App\Manager\AppEntityManager;
use App\Manager\TaskManager;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TaskManagerTest extends TestCase
{
    private TaskManager $taskManager;
    private AppEntityManager&MockObject $aem;
    private ProjectRepository&MockObject $projectRepository;
    private UserRepository&MockObject $userRepository;

    protected function setUp(): void
    {
        $this->aem = $this->createMock(AppEntityManager::class);
        $this->projectRepository = $this->createMock(ProjectRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userRepository->method('getReference')->willReturn(new User());

        $this->taskManager = new TaskManager($this->aem, $this->projectRepository, $this->userRepository);
    }

    public function testCreateFromData(): void
    {
        $worker = new User();
        $workerId = Uuid::v4()->toRfc4122();
        $worker->setId($workerId);
        $this->userRepository->expects($this->once())
            ->method('getReference')
            ->with($workerId)
            ->willReturn($worker);

        $data = new TaskAddData();
        $data->title = 'Test Task';
        $data->description = 'Test Description';
        $data->priority = TaskPriority::MEDIUM;
        $data->worker = $worker->getId();

        $this->aem->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Task::class));

        $task = $this->taskManager->createFromData($data);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->getTitle());
        $this->assertEquals('Test Description', $task->getDescription());
        $this->assertEquals(TaskPriority::MEDIUM, $task->getPriority());
    }

    public function testUpdateFromData(): void
    {
        $worker = new User();
        $workerId = Uuid::v4()->toRfc4122();
        $worker->setId($workerId);
        $this->userRepository->expects($this->once())
            ->method('getReference')
            ->with($workerId)
            ->willReturn($worker);

        $task = TaskFactory::create();
        $data = new TaskUpdateData();
        $data->title = 'Updated Task';
        $data->description = 'Updated Description';
        $data->priority = TaskPriority::HIGH;
        $data->worker = $worker->getId();

        $updatedTask = $this->taskManager->updateFromData($task, $data);

        $this->assertInstanceOf(Task::class, $updatedTask);
        $this->assertEquals('Updated Task', $updatedTask->getTitle());
        $this->assertEquals('Updated Description', $updatedTask->getDescription());
        $this->assertEquals(TaskPriority::HIGH, $updatedTask->getPriority());
    }

    public function testUpdateStatus(): void
    {
        $task = TaskFactory::create();
        $status = TaskStatus::COMPLETE;

        $updatedTask = $this->taskManager->updateStatus($task, $status);

        $this->assertInstanceOf(Task::class, $updatedTask);
        $this->assertEquals(TaskStatus::COMPLETE, $updatedTask->getStatus());
    }
}