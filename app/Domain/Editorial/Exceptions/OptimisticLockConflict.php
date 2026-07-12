<?php

namespace App\Domain\Editorial\Exceptions;

class OptimisticLockConflict extends InvalidEditorialOperation
{
    public function __construct(string $message = 'The catalog record changed after this operation began.')
    {
        parent::__construct($message, 'optimistic_lock_conflict');
    }
}
