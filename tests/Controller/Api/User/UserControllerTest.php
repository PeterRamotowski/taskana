<?php

namespace App\Tests\Controller\Api\User;

use App\Tests\AppWebTestCase;
use App\Repository\UserRepository;

class UserControllerTest extends AppWebTestCase
{
    public function testGetUser(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->findOneBy([]);
        $this->assertNotNull($user, 'No user found in the database.');
        $userId = $user->getId();
        $client->request('GET', '/api/user/' . $userId);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($userId->toRfc4122(), $data['id']);
        $this->assertEquals($user->getName(), $data['name']);
        $this->assertEquals($user->getEmail(), $data['email']);
    }
}
