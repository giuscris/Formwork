<?php

namespace Formwork\Data\Exceptions;

use RuntimeException;
use Throwable;

class InvalidValueException extends RuntimeException
{
    public function __construct(string $message, protected ?string $identifier = null, int $code = 0, ?Throwable $throwable = null)
    {
        parent::__construct($message, $code, $throwable);
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }
}
