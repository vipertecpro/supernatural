<?php

namespace App\Domain\Community\Exceptions;

use RuntimeException;

class InvalidCommunityOperation extends RuntimeException
{
    public function __construct(string $message, public readonly string $errorCode = 'invalid_community_operation')
    {
        parent::__construct($message);
    }
}
