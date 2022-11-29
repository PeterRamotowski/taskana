<?php

namespace App\Entity\Factory;

use App\Entity\Task;
use App\Entity\Enum\TaskStatus;
use Symfony\Component\Uid\Uuid;

class TaskFactory
{
  private Task $task;

  private function __construct()
  {
    $this->task = (new Task())
      ->setId(Uuid::v4())
      ->setStatus(TaskStatus::PENDING);
  }

  public static function create(): Task
  {
    return (new self())->task;
  }
}
