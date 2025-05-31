<?php

namespace App\Data;

use Symfony\Component\Validator\Constraints as Assert;

class ProjectAddData
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $title;

    #[Assert\Type('string')]
    public ?string $description = null;
}
