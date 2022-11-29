<?php

namespace App\Entity\Factory;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;

class UserFactory
{
  private User $user;

  private function __construct()
  {
    $this->user = (new User())
      ->setId(Uuid::v4())
      ->activate();
  }

  public static function create(): User
  {
    return (new self())->user;
  }
}
