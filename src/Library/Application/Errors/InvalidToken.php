<?php

namespace Library\Application\Errors;

final class InvalidToken extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Se requiere un token válido.');
    }
}
