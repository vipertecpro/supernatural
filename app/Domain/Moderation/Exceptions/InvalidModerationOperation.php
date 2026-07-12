<?php

namespace App\Domain\Moderation\Exceptions;

use RuntimeException;

class InvalidModerationOperation extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode = 'invalid_moderation_operation')
    {
        parent::__construct($message);
    }
}
