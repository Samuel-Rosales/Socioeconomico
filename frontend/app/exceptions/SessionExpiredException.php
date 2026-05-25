<?php

namespace App\Exceptions;

class SessionExpiredException extends \Exception
{
    public function __construct($message = 'Sesión expirada', $code = 403, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}