<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mensagens de erro HTTP
    |--------------------------------------------------------------------------
    |
    | Abaixo as mensagens padrão usadas nas mensagens de erro HTTP.
    |
    */

    '401' => [
        'titulo' => 'Acesso Não Autorizado',
        'mensagem' => 'Ooops!!! Suas credencias se perderam, tente se autenticar novamente.',
    ],
    '403' => [
        'titulo' => 'Acesso Proibido',
        'mensagem' => 'Ooops!!! Esse recurso não está disponível para você. Procure um administrador.',
    ],
    '404' => [
        'titulo' => 'Página Não Encontrada',
        'mensagem' => 'Ooops!!! Essa página não existe. Verifique a URL digitada.',
    ],
    '405' => [
        'titulo' => 'Método não Permitido',
        'mensagem' => 'Ooops!!! Esse método não é permitido para essa URL.',
    ],
    '419' => [
        'titulo' => 'Página Expirada',
        'mensagem' => 'Ooops!!! A sua requisição expirou. Se persistir, procure um administrador.',
    ],
    '429' => [
        'titulo' => 'Requisições em Excesso',
        'mensagem' => 'Ooops!!! Você fez mais requisições por segundo que o permitido pela aplicação.',
    ],
    '4xx' => [
        'titulo' => 'Erro no cliente',
        'mensagem' => 'Ooops!!! Parece haver algum problema com sua requisição. Se persistir, procure um administrador.',
    ],
    '500' => [
        'titulo' => 'Erro Interno',
        'mensagem' => 'Ooops!!! Salve-se quem puder, pois o servidor está com problemas graves. Procure um administrador.',
    ],
    '503' => [
        'titulo' => 'Serviço Indisponível',
        'mensagem' => 'Ooops!!! Os serviços estão indisponíveis. Tente novamente mais tarde.',
    ],
    '5xx' => [
        'titulo' => 'Erro no servidor',
        'mensagem' => 'Ooops!!! O servidor está tendo problemas internos para processar sua requisição. Procure um administrador.',
    ],
];
