<?php

namespace App\Validator;

use App\Manager\UserManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UserEmailNotExistsValidator extends ConstraintValidator
{
    public function __construct(
        private readonly UserManager $userManager,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UserEmailNotExists) {
            throw new UnexpectedTypeException($constraint, UserEmailNotExists::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->userManager->userExists($value)) {
            $this->context
                ->buildViolation($constraint->message)
                ->setInvalidValue($value)
                ->setParameter('{{ email }}', $value)
                ->addViolation();
        }
    }
}
