<?php

namespace App\Tests\Controller\Api\User;

use App\Repository\UserRepository;
use App\Tests\AppWebTestCase;

class UsersControllerTest extends AppWebTestCase
{
    public function testGetUsers(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $users = $userRepository->getList();
        if (empty($users)) {
            $this->markTestSkipped('No users found in the database.');
        }
        $client->request('GET', '/api/users');
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('email', $data[0]);
    }
}
