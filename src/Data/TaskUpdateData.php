<?php

namespace App\Data;

use App\Entity\Enum\TaskPriority;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class TaskUpdateData
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public Uuid $id;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $title;

    public ?string $description = null;

    #[Assert\NotBlank]
    #[Assert\Type(TaskPriority::class)]
    public TaskPriority $priority;

    public ?Uuid $project = null;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public Uuid $worker;
}
