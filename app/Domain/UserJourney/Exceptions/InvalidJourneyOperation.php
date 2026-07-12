<?php

namespace App\Domain\UserJourney\Exceptions;

use RuntimeException;

class InvalidJourneyOperation extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode = 'invalid_journey_operation')
    {
        parent::__construct($message);
    }
}
