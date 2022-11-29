<?php

namespace App\EventListener;

use App\Entity\User;
use App\Manager\AppEntityManager;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSuccessListener
{
    public function __construct(
        private readonly AppEntityManager $aem,
    ) {
    }

    #[AsEventListener(event: LoginSuccessEvent::class)]
    public function onLoginAttempt(LoginSuccessEvent $event): void
    {
        /** @var User $currentUser */
        $currentUser = $event->getUser();
        $currentUser->setLastLoginDate();
        $this->aem->refresh();
    }
}