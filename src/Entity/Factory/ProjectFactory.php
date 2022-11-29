<?php

namespace App\Entity\Factory;

use App\Entity\Project;
use Symfony\Component\Uid\Uuid;

class ProjectFactory
{
  private Project $project;

  private function __construct()
  {
    $this->project = (new Project())
      ->setId(Uuid::v4())
      ->unarchive();
  }

  public static function create(): Project
  {
    return (new self())->project;
  }
}
