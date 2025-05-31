<?php

namespace App\Data;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Uid\Uuid;

class CommentAddData
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $description;

    public ?Uuid $task = null;
}
