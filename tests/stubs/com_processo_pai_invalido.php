<?php

/**
 * Array com dados do processo para serem usados na geração do arquivo CSV.
 *
 * Particularidade: Apenas os campos do processo pai estão inválidos.
 */

return [
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        str_repeat('1', 26),    // Número do processo pai - Máximo 25 caracteres
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume inicial da caixa
        6,                      // Volume final da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
        'Criminal',             // Tipo de processo armazenado na caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '123',                  // Número do processo pai - Inválido
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume inicial da caixa
        6,                      // Volume final da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
        'Criminal',             // Tipo de processo armazenado na caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '84145187219344032589', // Número do processo pai - Inexistente
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume inicial da caixa
        6,                      // Volume final da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
        'Criminal',             // Tipo de processo armazenado na caixa
    ],
];
