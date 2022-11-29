<?php

namespace App\Response;

use App\Entity\User;

class UserCurrentResponse
{
    public function __construct(
        private readonly User $user
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function getPayload(): array
    {
        return [
          'user' => $this->user,
        ];
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return [
          'id' => $this->user->getId(),
          'email' => $this->user->getEmail(),
          'name' => $this->user->getName() ?: $this->user->getEmail(),
          'roles' => $this->user->getRoles(),
        ];
    }
}
