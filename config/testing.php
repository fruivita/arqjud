<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Username
    |--------------------------------------------------------------------------
    |
    | Usuário com permissão de login no domínio para propósito de testes
    | unitários. O usuário precisa ser funcional, isto é, não pode ser um.
    | usuário apenas de leitura.
    |
    */

    'username' => env('USERNAME', null),

    /*
    |--------------------------------------------------------------------------
    | Password
    |--------------------------------------------------------------------------
    |
    | Senha do usúario funcional para teste.
    */
    'password' => env('PASSWORD', false),
];
