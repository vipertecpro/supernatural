<?php

namespace App\Domain\Onboarding\Exceptions;

use RuntimeException;

class InvalidOnboardingTransition extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly string $errorCode = 'invalid_onboarding_transition',
    ) {
        parent::__construct($message);
    }
}
