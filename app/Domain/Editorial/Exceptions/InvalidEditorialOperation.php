<?php

namespace App\Domain\Editorial\Exceptions;

use DomainException;

class InvalidEditorialOperation extends DomainException
{
    public function __construct(string $message, public readonly string $errorCode = 'invalid_editorial_transition')
    {
        parent::__construct($message);
    }
}
