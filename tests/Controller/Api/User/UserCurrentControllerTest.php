<?php

namespace App\Tests\Controller\Api\User;

use App\Tests\AppWebTestCase;
use App\Repository\UserRepository;

class UserCurrentControllerTest extends AppWebTestCase
{
    public function testGetCurrentUser(): void
    {
        $client = $this->authorize();
        $container = static::getContainer();
        $tokenStorage = $container->get('security.token_storage');
        $token = $tokenStorage->getToken();
        $user = $token ? $token->getUser() : null;
        $this->assertNotNull($user, 'No authenticated user found.');
        $client->request('GET', '/api/user/current');
        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($user->getEmail(), $data['email']);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
    }

    public function testGetCurrentUserNoAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/user/current');
        $response = $client->getResponse();
        $this->assertEquals(302, $response->getStatusCode());
    }
}
