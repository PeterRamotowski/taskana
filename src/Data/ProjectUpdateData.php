<?php

namespace App\Data;

use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectUpdateData
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public Uuid $id;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $title;

    #[Assert\Type('string')]
    public ?string $description = null;
}
