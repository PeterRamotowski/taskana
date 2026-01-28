<?php

namespace App\Data;

use App\Entity\Enum\RecurrencePattern;
use App\Entity\Enum\TaskPriority;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class TaskAddData
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $title;

    public ?string $description = null;

    #[Assert\NotBlank]
    public TaskPriority $priority;

    public ?Uuid $project = null;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public Uuid $worker;

    public ?float $estimatedHours = null;

    public ?string $dueDate = null;

    public bool $isRecurring = false;

    public ?RecurrencePattern $recurrencePattern = null;

    public ?int $recurrenceInterval = 1;

    public ?string $recurrenceEndDate = null;
}
