<?php

namespace App\Entity;

use Symfony\Component\Uid\Uuid;

interface EntityInterface
{
    public function getId(): Uuid;
}
