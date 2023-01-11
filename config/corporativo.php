<?php

return [
    /*
    | ------------------------------------------------------------------------
    | Max Upsert
    | ------------------------------------------------------------------------
    |
    | Número de registros que serão persistidos no banco de dados por query.
    |
    | Se menor ou igual a zero, utilizará o valor padrão internamente definido.
    |
    */
    'max_upsert' => 500,

    /*
    | ------------------------------------------------------------------------
    | Padrão matrícula
    | ------------------------------------------------------------------------
    |
    | Valor a ser, eventualmente, adicionado ao início da matrícula caso.
    |
    | necessário. Ex.: ES99999
    |
    */
    'matricula' => 'ES',

    /*
    | ------------------------------------------------------------------------
    | Emitir eventos
    | ------------------------------------------------------------------------
    |
    | Se o processo de importação deve emitir os eventos de início e fim da
    | importação e de alteração da lotação, função ou cargo do usuário.
    |
    */
    'eventos' => true,
];
