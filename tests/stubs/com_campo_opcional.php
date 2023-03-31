<?php

/**
 * Array com dados do processo para serem usados na geração do arquivo CSV.
 *
 * Particularidade: Todos os campos opcionais estão presentes, exceto o
 * processo pai.
 */

return [[
    '26899909319841005657', // Número do processo
    '0944643060',           // Número do processo antigo
    '',                     // Número do processo pai
    '21-12-2020',           // Data de arquivamento do processo
    2,                      // Qte de volumes do processo
    5,                      // Número da caixa
    'foo',                  // Tipo/Complemento da caixa
    2020,                   // Ano da caixa
    3,                      // Volume inicial da caixa
    6,                      // Volume final da caixa
    'Sim',                  // Processo de guarda permanente
    'Yokohama',             // Nome da localidade de origem/criação da caixa
    'Madrid',               // Nome da localidade
    'Empire State',         // Nome do prédio
    10,                     // Número do Andar
    '100-s',                // Número da Sala
    '20-e',                 // Número da Estante
    '30-p',                 // Número da Prateleita
    'Loren ipsum',          // Observação/Descrição da caixa
    'Criminal',             // Tipo de processo armazenado na caixa
]];
