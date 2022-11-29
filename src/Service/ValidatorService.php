<?php

namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService
{
	public function __construct(
		private readonly ValidatorInterface $validator
	) {
	}

	public function validate(mixed $data): void
	{
		if (count($errors = $this->validator->validate($data))) {
			throw new ValidationException($errors);
		}
	}
}
