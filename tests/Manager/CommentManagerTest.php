<?php

namespace App\Tests\Manager;

use App\Data\CommentAddData;
use App\Entity\Comment;
use App\Entity\Task;
use App\Manager\AppEntityManager;
use App\Manager\CommentManager;
use App\Repository\TaskRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CommentManagerTest extends TestCase
{
    private CommentManager $commentManager;
    private AppEntityManager&MockObject $aem;
    private TaskRepository&MockObject $taskRepository;

    protected function setUp(): void
    {
        $this->aem = $this->createMock(AppEntityManager::class);
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->commentManager = new CommentManager($this->aem, $this->taskRepository);
    }

    public function testCreateFromDataSavesComment(): void
    {
        $data = new CommentAddData();
        $data->description = 'Test comment';
        $data->task = Uuid::v4();

        $task = $this->createMock(Task::class);
        $this->taskRepository->expects($this->once())
            ->method('getReference')
            ->with($data->task)
            ->willReturn($task);
        $this->aem->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Comment::class));

        $comment = $this->commentManager->createFromData($data);
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('Test comment', $comment->getDescription());
        $this->assertSame($task, $comment->getTask());
    }

    public function testCreateFromDataWithoutTask(): void
    {
        $data = new CommentAddData();
        $data->description = 'No task';
        $data->task = null;

        $this->aem->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Comment::class));
        $this->taskRepository->expects($this->never())
            ->method('getReference');

        $comment = $this->commentManager->createFromData($data);
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('No task', $comment->getDescription());
        $this->assertNull($comment->getTask());
    }

    public function testCreateFromDataWithSaveFalse(): void
    {
        $data = new CommentAddData();
        $data->description = 'No save';
        $data->task = null;

        $this->aem->expects($this->never())
            ->method('save');

        $comment = $this->commentManager->createFromData($data, false);
        $this->assertInstanceOf(Comment::class, $comment);
        $this->assertEquals('No save', $comment->getDescription());
    }
}
