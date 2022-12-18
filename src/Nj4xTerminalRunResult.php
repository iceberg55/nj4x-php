<?php

namespace D4T\Nj4x;

final class Nj4xTerminalRunResult
{
    private int $code;
    private ?string $message;

    public function __construct( int $code, string $message = null)
    {
        $this->code = $code;
        $this->message = $message;
    }

    public function getCode() : int
    {
        return $this->code;
    }

    public function getMessage() : ?string
    {
        return $this->message;
    }
}
