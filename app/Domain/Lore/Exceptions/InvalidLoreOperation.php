<?php

namespace App\Domain\Lore\Exceptions;

use DomainException;

class InvalidLoreOperation extends DomainException
{
    public function __construct(string $message, public readonly string $errorCode = 'invalid_lore_operation')
    {
        parent::__construct($message);
    }
}
