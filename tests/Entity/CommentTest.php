<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class CommentTest extends TestCase
{
    private Comment $comment;
    private string $testUuid;
    private string $testDescription;

    protected function setUp(): void
    {
        $this->testUuid = Uuid::v4()->toRfc4122();
        $this->testDescription = 'This is a test comment.';
        $this->comment = new Comment();
    }

    public function testConstructor(): void
    {
        $this->assertNull($this->comment->getTask());
    }

    public function testIdGetterAndSetter(): void
    {
        $this->comment->setId($this->testUuid);
        $this->assertInstanceOf(Uuid::class, $this->comment->getId());
        $this->assertEquals($this->testUuid, $this->comment->getId()->toRfc4122());
    }

    public function testDescriptionGetterAndSetter(): void
    {
        $this->comment->setDescription($this->testDescription);
        $this->assertEquals($this->testDescription, $this->comment->getDescription());
    }

    public function testAuthorGetterAndSetter(): void
    {
        $author = new User();
        $this->comment->setAuthor($author);
        $this->assertSame($author, $this->comment->getAuthor());
    }

    public function testTaskGetterAndSetter(): void
    {
        $task = new Task();
        $this->comment->setTask($task);
        $this->assertSame($task, $this->comment->getTask());
        $this->comment->setTask(null);
        $this->assertNull($this->comment->getTask());
    }

    public function testFluentInterface(): void
    {
        $author = new User();
        $task = new Task();
        $result = $this->comment
            ->setId($this->testUuid)
            ->setDescription($this->testDescription)
            ->setAuthor($author)
            ->setTask($task);
        $this->assertSame($this->comment, $result);
    }

    public function testDefaultValues(): void
    {
        $comment = new Comment();
        $this->assertNull($comment->getTask());
    }
}
