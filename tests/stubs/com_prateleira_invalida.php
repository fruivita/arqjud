<?php

/**
 * Array com dados do processo para serem usados na geração do arquivo CSV.
 *
 * Particularidade: Apenas os campos da prateleira estão inválidos.
 */

return [[
    '26899909319841005657', // Número do processo
    '',                     // Número do processo antigo
    '',                     // Número do processo pai
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
    str_repeat('a', 101),   // Número da Prateleira - Máximo 100 caracteres
    '',                     // Observação/Descrição da caixa
    'Criminal',             // Tipo de processo armazenado na caixa
]];
