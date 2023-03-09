<?php

use App\Models\Processo;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

if (!function_exists('ascOrDesc')) {
    /**
     * Determina o valor que deve ser utilizado na ordenção das queries de
     * acordo com a direção informada.
     *
     * Desc é a ordenação padrão, pois, por padrão, os registros são exibidos
     * do mais recente para o mais antigo.
     *
     * @return string `asc` ou `desc` (default)
     */
    function ascOrDesc(string $direcao = null)
    {
        return str($direcao)->lower()->exactly('asc') ? 'asc' : 'desc';
    }
}

if (!function_exists('mascara')) {
    /**
     * Aplica determinada máscara à string informada. Se a máscara não for
     * compatível com a string informada, retorna-a sem o mascaramento.
     *
     * Exemplo: 1234567 com mascara ##.##-### retornará 12.34-567
     * Exemplo: 123 com mascara ##.##-### retornará 123
     *
     * @return string
     */
    function mascara(string $valor, string $mascara)
    {
        $mascara = str($mascara);

        if ($mascara->substrCount('#') !== str($valor)->length()) {
            return $valor;
        }

        return sprintf(
            $mascara->replace('#', '%s')->toString(),
            ...str_split($valor)
        );
    }
}

if (!function_exists('cnj')) {
    /**
     * Aplica a máscara CNJ no número informado.
     *
     * Exemplo: 11111111111111111111 retornará 1111111-11.1111.1.11.1111
     *
     * @return string
     */
    function cnj(string $valor)
    {
        return mascara($valor, Processo::MASCARA_CNJ);
    }
}

if (!function_exists('v1')) {
    /**
     * Aplica a máscara v1 (processo de 10 digitos) no número informado.
     *
     * Exemplo: 1111111111 retornará 11.1111111-1
     *
     * @return string
     */
    function v1(string $valor)
    {
        return mascara($valor, Processo::MASCARA_V1);
    }
}

if (!function_exists('v2')) {
    /**
     * Aplica a máscara v2 (processo de 15 digitos) no número informado.
     *
     * Exemplo: 111111111111111 retornará 1111.11.11.111111-1
     *
     * @return string
     */
    function v2(string $valor)
    {
        return mascara($valor, Processo::MASCARA_V2);
    }
}

if (!function_exists('apenasNumeros')) {
    /**
     * Retorna apenas a parte numérica da string informada ou, se nada sobrar,
     * null.
     *
     * Exemplo: 123.456-AB retornará 123456
     *
     * @return string|null
     */
    function apenasNumeros(string $valor = null)
    {
        $numeros = str($valor)->replaceMatches('/[^0-9]++/', '')->toString();

        return $numeros ?: null;
    }
}

if (!function_exists('dataCompleta')) {
    /**
     * Converte a data informada no formato para assinatura de documentos.
     *
     * Ex.: Segunda-feira, 10 de agosto de 2020
     *
     * @return string
     */
    function dataCompleta(Carbon $data)
    {
        $dia_semana = str(diaDaSemana($data->dayOfWeek))->ucfirst()->toString();
        $mes = mes($data->month);

        return "{$dia_semana}, {$data->day} de {$mes} de {$data->year}";
    }
}

if (!function_exists('diaDaSemana')) {
    /**
     * Dia da semana por extenso para o número informado.
     *
     * Ex.: 0 - domingo, ..., 6 - sábado
     *
     * @return string
     */
    function diaDaSemana(int $numero)
    {
        return Arr::get([
            'domingo',
            'segunda-feira',
            'terça-feira',
            'quarta-feira',
            'quinta-feira',
            'sexta-feira',
            'sábado',
        ], $numero);
    }
}

if (!function_exists('mes')) {
    /**
     * Mês por extenso para o número informado.
     *
     * Ex.: 1 - janeiro, ..., 12 - dezembro.
     *
     * @return string
     */
    function mes(int $numero)
    {
        return Arr::get([
            'janeiro',
            'fevereiro',
            'março',
            'abril',
            'maio',
            'junho',
            'julho',
            'agosto',
            'setembro',
            'outubro',
            'novembro',
            'dezembro',
        ], $numero - 1);
    }
}

if (!function_exists('injetarTotalPagina')) {
    /**
     * Substitui o placeholder predefinido com o número total de páginas em
     * todo o documento.
     *
     * @return void
     */
    function injetarTotalPagina(PDF $dompdf)
    {
        /** @var \Dompdf\Adapter\CPDF $canvas */
        $canvas = $dompdf->getCanvas();
        $pdf = $canvas->get_cpdf();

        foreach ($pdf->objects as &$o) {
            if ($o['t'] === 'contents') {
                $o['c'] = str_replace('^TP^', (string) $canvas->get_page_count(), $o['c']);
            }
        }
    }
}
