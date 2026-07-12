<?php

namespace App\Domain\Media\Exceptions;

use RuntimeException;

class InvalidMediaOperation extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode = 'invalid_media_operation')
    {
        parent::__construct($message);
    }
}
