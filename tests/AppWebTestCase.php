<?php

namespace App\Tests;

use App\DataFixtures\UserFixtures;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class AppWebTestCase extends WebTestCase
{
    /**
     * @param string $environment
     * @return void
     */
    public function init(string $environment = 'test'): void
    {
        static::bootKernel([
            'environment' => $environment
        ]);
    }

    /**
     * @return AbstractBrowser|null
     */
    public function authorize(): ?AbstractBrowser
    {
        $client = static::createClient();
        $container = static::getContainer();
        $userRepository = $container->get(UserRepository::class);

        $email = UserFixtures::getUsersData()[0]['email'];
        $user = $userRepository->findOneByEmail($email);

        $client->loginUser($user, 'admin');

        return $client;
    }
}
