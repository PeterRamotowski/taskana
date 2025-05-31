<?php

namespace App\Tests\Manager;

use App\Data\UserAddData;
use App\Entity\User;
use App\Manager\AppEntityManager;
use App\Manager\UserManager;
use App\Repository\UserRepository;
use App\Service\PasswordHasherService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class UserManagerTest extends TestCase
{
    private UserManager $userManager;
    private AppEntityManager&MockObject $aem;
    private UserRepository&MockObject $userRepository;
    private PasswordHasherService&MockObject $passwordHasher;

    protected function setUp(): void
    {
        $this->aem = $this->createMock(AppEntityManager::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->passwordHasher = $this->createMock(PasswordHasherService::class);
        $this->userManager = new UserManager($this->aem, $this->userRepository, $this->passwordHasher);
    }

    public function testUserExistsReturnsTrueIfUserFound(): void
    {
        $this->userRepository->method('getUserByEmail')->willReturn(new User());
        $this->assertTrue($this->userManager->userExists('test@example.com'));
    }

    public function testUserExistsReturnsFalseIfUserNotFound(): void
    {
        $this->userRepository->method('getUserByEmail')->willReturn(null);
        $this->assertFalse($this->userManager->userExists('notfound@example.com'));
    }

    public function testUpdatePassword(): void
    {
        $user = new User();
        $this->passwordHasher->expects($this->once())
            ->method('hash')
            ->with('newpass')
            ->willReturn('hashedpass');
        $this->aem->expects($this->once())->method('flush');
        $user->setPassword('oldpass');
        $updated = $this->userManager->updatePassword($user, 'newpass');
        $this->assertEquals('hashedpass', $updated->getPassword());
    }

    public function testUpdateRoles(): void
    {
        $user = new User();
        $roles = ['ROLE_USER'];
        $this->aem->expects($this->once())->method('flush');
        $user->setRoles([]);
        $updated = $this->userManager->updateRoles($user, $roles);
        $this->assertEquals($roles, $updated->getRoles());
    }

    public function testCreateFromData(): void
    {
        $data = new UserAddData();
        $data->email = 'test@example.com';
        $data->password = 'password';
        $data->roles = ['ROLE_USER'];
        $this->passwordHasher->method('hash')->willReturn('hashedpass');
        $user = $this->userManager->createFromData($data);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('hashedpass', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }
}
