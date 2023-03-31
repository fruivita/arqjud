<?php

/**
 * Array com dados do processo para serem usados na geração do arquivo CSV.
 *
 * Particularidade: Apenas o ano da caixa é inválido, pois futuro.
 */

return [
    [
        '26899909319841005657',        // Número do processo
        '',                            // Número do processo antigo
        '',                            // Número do processo pai
        '21-12-2020',                  // Data de arquivamento do processo
        2,                             // Qte de volumes do processo
        '10',                          // Número da caixa
        '',                            // Tipo/Complemento da caixa
        now()->addYear()->format('Y'), // Ano da caixa - Máximo ano corrente
        3,                             // Volume inicial da caixa
        6,                             // Volume final da caixa
        'Não',                         // Processo de guarda permanente
        'Yokohama',                    // Nome da localidade de origem/criação da caixa
        'Madrid',                      // Nome da localidade
        'Empire State',                // Nome do prédio
        10,                            // Número do Andar
        '100-s',                       // Número da Sala
        '',                            // Número da Estante
        '',                            // Número da Prateleita
        '',                            // Observação/Descrição da caixa
        'Criminal',                    // Tipo de processo armazenado na caixa
    ],
];
