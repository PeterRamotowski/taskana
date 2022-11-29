<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UserEmailNotExists extends Constraint
{
    public string $message = 'This email address already exists.';
}
