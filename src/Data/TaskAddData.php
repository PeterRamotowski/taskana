<?php

namespace App\Data;

use App\Entity\Enum\TaskPriority;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class TaskAddData
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $title;

    public ?string $description;

    #[Assert\NotBlank]
    public TaskPriority $priority;

    public ?Uuid $project;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public Uuid $worker;
}
