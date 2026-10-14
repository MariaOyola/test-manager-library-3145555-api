<?php

namespace Library\Application\Errors;

final class InvalidCredentials extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Credenciales inválidas.');
    }
}
