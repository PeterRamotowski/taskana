<?php

namespace App\DataFixtures;

use App\Data\UserAddData;
use App\Manager\AppEntityManager;
use App\Manager\UserManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly AppEntityManager $aem,
        private readonly UserManager $userManager,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $users = [
            [
                'email' => 'taskana@fkv.pl',
                'password' => 'adminpassword',
                'roles' => ['ROLE_ADMIN']
            ],
            [
                'email' => 'taskana+user1@fkv.pl',
                'password' => 'password1',
                'roles' => []
            ],
            [
                'email' => 'taskana+user2@fkv.pl',
                'password' => 'password2',
                'roles' => []
            ]
        ];

        foreach ($users as $index => $userData) {
            $userAddData = new UserAddData();
            $userAddData->email = $userData['email'];
            $userAddData->password = $userData['password'];
            $userAddData->roles = $userData['roles'];

            $user = $this->userManager->createFromData($userAddData);
            $this->aem->persist($user);

            $this->addReference('user_'.($index + 1), $user);
        }

        $this->aem->flush();
    }
}
