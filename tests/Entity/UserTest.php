<?php

namespace App\Tests\Entity;

use App\Entity\Comment;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    private User $user;
    private string $testUuid;
    private string $testEmail;
    private string $testPassword;
    private string $testName;
    private array $testRoles;

    protected function setUp(): void
    {
        $this->testUuid = Uuid::v4()->toRfc4122();
        $this->testEmail = 'test@example.com';
        $this->testPassword = 'password123';
        $this->testName = 'Test User';
        $this->testRoles = ['ROLE_ADMIN'];
        $this->user = new User();
    }

    public function testConstructor(): void
    {
        $this->assertInstanceOf(Collection::class, $this->user->getAssignedTasks());
        $this->assertInstanceOf(Collection::class, $this->user->getCreatedTasks());
        $this->assertInstanceOf(Collection::class, $this->user->getComments());
        $this->assertCount(0, $this->user->getAssignedTasks());
        $this->assertCount(0, $this->user->getCreatedTasks());
        $this->assertCount(0, $this->user->getComments());
    }

    public function testIdGetterAndSetter(): void
    {
        $this->user->setId($this->testUuid);
        $this->assertInstanceOf(Uuid::class, $this->user->getId());
        $this->assertEquals($this->testUuid, $this->user->getId()->toRfc4122());
    }

    public function testEmailGetterAndSetter(): void
    {
        $this->user->setEmail($this->testEmail);
        $this->assertEquals($this->testEmail, $this->user->getEmail());
        $this->assertEquals($this->testEmail, $this->user->getUserIdentifier());
    }

    public function testPasswordGetterAndSetter(): void
    {
        $this->user->setPassword($this->testPassword);
        $this->assertEquals($this->testPassword, $this->user->getPassword());
    }

    public function testNameGetterAndSetter(): void
    {
        $this->user->setName($this->testName);
        $this->assertEquals($this->testName, $this->user->getName());
        $this->assertEquals($this->testName, $this->user->getUsername());
    }

    public function testRolesGetterAndSetter(): void
    {
        $this->user->setRoles($this->testRoles);
        $roles = $this->user->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertContains('ROLE_USER', $roles); // always present
    }

    public function testActiveGetterAndSetter(): void
    {
        $this->user->setActive(true);
        $this->assertTrue($this->user->isActive());
        $this->user->setActive(false);
        $this->assertFalse($this->user->isActive());
    }

    public function testActivate(): void
    {
        $this->user->setActive(false);
        $this->user->activate();
        $this->assertTrue($this->user->isActive());
    }

    public function testLastLoginDateGetterAndSetter(): void
    {
        $date = new \DateTime('+1 day');
        $this->user->setLastLoginDate($date);
        $this->assertEquals($date, $this->user->getLastLoginDate());
        $this->user->setLastLoginDate(null);
        $this->assertNull($this->user->getLastLoginDate());
    }

    public function testAssignedTasks(): void
    {
        $task = new Task();
        $result = $this->user->addAssignedTask($task);
        $this->assertCount(1, $this->user->getAssignedTasks());
        $this->assertContains($task, $this->user->getAssignedTasks());
        $this->assertSame($this->user, $result);
    }

    public function testCreatedTasks(): void
    {
        $task = new Task();
        $result = $this->user->addCreatedTask($task);
        $this->assertCount(1, $this->user->getCreatedTasks());
        $this->assertContains($task, $this->user->getCreatedTasks());
        $this->assertSame($this->user, $result);
    }

    public function testComments(): void
    {
        $comment = new Comment();
        $result = $this->user->addComment($comment);
        $this->assertCount(1, $this->user->getComments());
        $this->assertContains($comment, $this->user->getComments());
        $this->assertSame($this->user, $result);
    }

    public function testJsonSerialize(): void
    {
        $this->user->setId($this->testUuid);
        $this->user->setEmail($this->testEmail);
        $this->user->setName($this->testName);
        $this->user->setRoles($this->testRoles);
        $json = $this->user->jsonSerialize();
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('username', $json);
        $this->assertArrayHasKey('roles', $json);
        $this->assertEquals($this->testName, $json['name']);
        $this->assertEquals($this->testName, $json['username']);
        $this->assertContains('ROLE_ADMIN', $json['roles']);
        $this->assertContains('ROLE_USER', $json['roles']);
    }

    public function testFluentInterface(): void
    {
        $date = new \DateTime();
        $result = $this->user
            ->setId($this->testUuid)
            ->setEmail($this->testEmail)
            ->setPassword($this->testPassword)
            ->setName($this->testName)
            ->setRoles($this->testRoles)
            ->setActive(true)
            ->setLastLoginDate($date);
        $this->assertSame($this->user, $result);
    }
}
