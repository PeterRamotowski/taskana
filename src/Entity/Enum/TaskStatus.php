<?php

namespace App\Entity\Enum;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case COMPLETE = 'complete';
}