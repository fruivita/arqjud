<?php

/**
 * Array com dados do processo para serem usados na geração do arquivo CSV.
 *
 * Particularidade: Apenas os campos do processo estão inválidos.
 */

return [
    [
        '',                     // Número do processo - Obrigatório
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '2689990931984100565',  // Número do processo - Inválido
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        str_repeat('1', 26),    // Número do processo - Máximo 25 caracteres
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        str_repeat('1', 26),    // Número do processo antigo - Máximo 25 caracteres
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '123',                  // Número do processo antigo - Inválido
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        '',                     // Qte de volumes do processo - Obrigatório
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        0,                      // Qte de volumes do processo - Mínimo 1
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        10000,                  // Qte de volumes do processo - Máximo 9999
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'Não',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        '',                     // Processo de guarda permanente - Obrigatório
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '21-12-2020',           // Data de arquivamento do processo
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'foo',                  // Processo de guarda permanente - Opção inválida
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '',                     // Data de arquivamento do processo - Obrigatório
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'sim',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '2020-12-21',           // Data de arquivamento do processo - Formato inválido
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'sim',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
    [
        '26899909319841005657', // Número do processo
        '',                     // Número do processo antigo
        '',                     // Número do processo pai
        '31-12-1899',           // Data de arquivamento do processo - Mínimo 01-01-1900
        2,                      // Qte de volumes do processo
        5,                      // Número da caixa
        '',                     // Tipo/Complemento da caixa
        2020,                   // Ano da caixa
        3,                      // Volume da caixa
        'sim',                  // Processo de guarda permanente
        'Yokohama',             // Nome da localidade de origem/criação da caixa
        'Madrid',               // Nome da localidade
        'Empire State',         // Nome do prédio
        10,                     // Número do Andar
        '100-s',                // Número da Sala
        '',                     // Número da Estante
        '',                     // Número da Prateleita
        '',                     // Observação/Descrição da caixa
    ],
];
