<?php

namespace App\Faker;

use Faker\Provider\Base;

/**
 * Gerador de números de processos judiciais brasileiros no padrão definido
 * pelo Conselho Nacional de Justiça.
 *
 * @see https://atos.cnj.jus.br/atos/detalhar/119
 * @see https://atos.cnj.jus.br/files/resolucao_65_16122008_04032013165912.pdf
 * @see https://hofmannsven.com/2021/faker-provider-in-laravel
 */
class NumeroProcessoCNJProvider extends Base
{
    /**
     * Gera números de processos judiciais brasileiros sem aplicação de
     * máscara, isto é, apenas a string numérica.
     *
     * Os números gerados são fictícios. Se verídicos, terá sido uma mera
     * coincidência.
     *
     * Art. 1º Fica instituída a numeração única de processos no âmbito do
     * Poder Judiciário, observada a estrutura NNNNNNN-DD.AAAA.J.TR.OOOO,
     * composta de 6 (seis) campos obrigatórios, nos termos da tabela
     * padronizada constante dos Anexos I a VII desta Resolução.
     *
     * Exemplo: 00001001520081000000
     *
     * @return string
     */
    public static function numeroProcessoCNJ()
    {
        $sequencial = self::sequencial();
        $ano = self::ano();
        $orgao = self::orgao();
        $tribunal = self::tribunal($orgao);
        $unidade_origem = self::unidadeOrigem();
        $digito_verificador = self::gerarDigitoVerificador(
            $sequencial,
            $ano,
            $orgao,
            $tribunal,
            $unidade_origem
        );

        return "{$sequencial}{$digito_verificador}{$ano}{$orgao}{$tribunal}{$unidade_origem}";
    }

    /**
     * Retorna um número de 7 dígitos com tantos zeros à esquerda quanto forem
     * necessários.
     *
     * 1º O campo (NNNNNNN), com 7 (sete) dígitos, identifica o número
     * seqüencial do processo por unidade de origem (OOOO), a ser reiniciado a
     * cada ano, facultada a utilização de funcionalidade que oculte a
     * visibilidade dos zeros à esquerda e/ou torne desnecessário o seu
     * preenchimento para a localização do processo.
     *
     * Exemplo: 0000100
     *
     * @return string
     */
    private static function sequencial()
    {
        return static::numerify('#######');
    }

    /**
     * Ano de ajuizamento da ação.
     *
     * § 3º O campo (AAAA), com 4 (quatro) dígitos, identifica o ano do
     * ajuizamento do processo.
     *
     * @return int
     */
    private static function ano()
    {
        return static::numberBetween(1900, intval(date('Y')));
    }

    /**
     * Órgão de ajuizamento da ação.
     *
     * § 4º O campo (J), com 1 (um) dígito, identifica o órgão ou segmento do
     * Poder Judiciário, observada a seguinte correspondência:
     *
     * I – Supremo Tribunal Federal: 1 (um);
     * II – Conselho Nacional de Justiça: 2 (dois);
     * III – Superior Tribunal de Justiça: 3 (três);
     * IV - Justiça Federal: 4 (quatro);
     * V - Justiça do Trabalho: 5 (cinco);
     * VI - Justiça Eleitoral: 6 (seis);
     * VII - Justiça Militar da União: 7 (sete);
     * VIII - Justiça dos Estados e do Distrito Federal e Territórios: 8 (oito);
     * IX - Justiça Militar Estadual: 9 (nove).
     *
     * @return int
     */
    private static function orgao()
    {
        return static::numberBetween(1, 9);
    }

    /**
     * Tribunal de ajuizamento da ação.
     *
     * § 5º O campo (TR), com 2 (dois) dígitos, identifica o tribunal do
     * respectivo segmento do Poder Judiciário e, na Justiça Militar da União,
     * a Circunscrição Judiciária, observando-se:
     *
     * I – nos processos originários do Supremo Tribunal Federal, do Conselho
     * Nacional de Justiça, do Superior Tribunal de Justiça, do Tribunal
     * Superior do Trabalho, do Tribunal Superior Eleitoral e do Superior
     * Tribunal Militar, o campo (TR) deve ser preenchido com zero;
     *
     * II – nos processos originários do Conselho da Justiça Federal e do
     * Conselho Superior da Justiça do Trabalho, o campo (TR) deve ser
     * preenchido com o número 90 (noventa);
     *
     * III – nos processos da Justiça Federal, os Tribunais Regionais Federais
     * devem ser identificados no campo (TR) pelos números 01 a 05, observadas
     * as respectivas regiões;
     *
     * IV – nos processos da Justiça do Trabalho, os Tribunais Regionais do
     * Trabalho devem ser identificados no campo (TR) pelos números 01 a 24,
     * observadas as respectivas regiões;
     *
     * V – nos processos da Justiça Eleitoral, os Tribunais Regionais
     * Eleitorais devem ser identificados no campo (TR) pelos números 01 a 27,
     * observados os Estados da Federação, em ordem alfabética;
     *
     * VI – nos processos da Justiça Militar da União, as Circunscrições
     * Judiciárias Militares devem ser identificadas no campo (TR) pelos
     * números 01 a 12, observada a subdivisão vigente;
     *
     * VII – nos processos da Justiça dos Estados e do Distrito Federal e
     * Territórios, os Tribunais de Justiça devem ser identificados no campo
     * (TR) pelos números 01 a 27, observados os Estados da Federação e o
     * Distrito Federal, em ordem alfabética;
     *
     * VIII – nos processos da Justiça Militar Estadual, os Tribunais Militares
     * dos Estados de Minas Gerais, Rio Grande do Sul e São Paulo devem ser
     * identificados no campo (TR) pelos números 13, 21 e 26, respectivamente,
     * cumprida a ordem alfabética de que tratam os incisos V e VII;
     *
     * @return string
     */
    private static function tribunal(int $orgao)
    {
        $tribunal = null;

        switch ($orgao) {
            case 1: // Supremo Tribunal Federal
            case 2: // Conselho Nacional de Justiça
            case 3: // Superior Tribunal de Justiça
                $tribunal = self::tribunalSuperior();

                break;
            case 4: // Justiça Federal
                $tribunal = self::justicaFederal();

                break;
            case 5: // Justiça do Trabalho
                $tribunal = self::justicaTrabalho();

                break;
            case 6: // Justiça Eleitoral
                $tribunal = self::justicaEleitoral();

                break;
            case 7: // Justiça Militar da União
                $tribunal = self::justicaMilitarUniao();

                break;
            case 8: // Justiça dos Estados e do Distrito Federal e Territórios
                $tribunal = self::justicaEstadual();

                break;
            case 9: // Justiça Militar Estadual
                $tribunal = self::justicaMilitarEstadual();

                break;
        }

        return $tribunal;
    }

    /**
     * Retorna o código do tribunal (TR) para os processos originários dos
     * Tribunais Superiores.
     *
     * I – nos processos originários do Supremo Tribunal Federal, do Conselho
     * Nacional de Justiça, do Superior Tribunal de Justiça, do Tribunal
     * Superior do Trabalho, do Tribunal Superior Eleitoral e do Superior
     * Tribunal Militar, o campo (TR) deve ser preenchido com zero;
     *
     * @return string
     */
    private static function tribunalSuperior()
    {
        return '00';
    }

    /**
     * Retorna o código do tribunal (TR) para os processos originários da
     * Justiça Federal.
     *
     * II – nos processos originários do Conselho da Justiça Federal [...], o
     * campo (TR) deve ser preenchido com o número 90 (noventa);
     *
     * III – nos processos da Justiça Federal, os Tribunais Regionais Federais
     * devem ser identificados no campo (TR) pelos números 01 a 05, observadas
     * as respectivas regiões;
     *
     * @return string
     */
    private static function justicaFederal()
    {
        return static::randomElement([
            '90', // CJF
            '01', // 1ª Região
            '02', // 2ª Região
            '03', // 3ª Região
            '04', // 4ª Região
            '05', // 5ª Região
        ]);
    }

    /**
     * Retorna o código do tribunal (TR) para os processos originários da
     * Justiça do Trabalho.
     *
     * I – nos processos originários [...] do Tribunal Superior do Trabalho,
     * [...] o campo (TR) deve ser preenchido com zero;
     *
     * II – nos processos originários [...] do Conselho Superior da Justiça do
     * Trabalho, o campo (TR) deve ser preenchido com o número 90 (noventa);
     *
     * IV – nos processos da Justiça do Trabalho, os Tribunais Regionais do
     * Trabalho devem ser identificados no campo (TR) pelos números 01 a 24,
     * observadas as respectivas regiões;
     *
     * @return string
     */
    private static function justicaTrabalho()
    {
        return static::randomElement([
            '00', // TST
            '90', // CSJT
            '01', //  1ª Região
            '02', //  2ª Região
            '03', //  3ª Região
            '04', //  4ª Região
            '05', //  5ª Região
            '06', //  6ª Região
            '07', //  7ª Região
            '08', //  8ª Região
            '09', //  9ª Região
            '10', // 10ª Região
            '11', // 11ª Região
            '12', // 12ª Região
            '13', // 13ª Região
            '14', // 14ª Região
            '15', // 15ª Região
            '16', // 16ª Região
            '17', // 17ª Região
            '18', // 18ª Região
            '19', // 19ª Região
            '20', // 20ª Região
            '21', // 21ª Região
            '22', // 22ª Região
            '23', // 23ª Região
            '24', // 24ª Região
        ]);
    }

    /**
     * Retorna o código do tribunal (TR) para os processos originários da
     * Justiça Eleitoral.
     *
     * I – nos processos originários [...] do Tribunal Superior Eleitoral [...]
     * o campo (TR) deve ser preenchido com zero;
     *
     * V – nos processos da Justiça Eleitoral, os Tribunais Regionais
     * Eleitorais devem ser identificados no campo (TR) pelos números 01 a 27,
     * observados os Estados da Federação, em ordem alfabética;
     *
     * @return string
     */
    private static function justicaEleitoral()
    {
        return static::randomElement([
            '00', // TSE
            '01', // TRE-AC
            '02', // TRE-AL
            '03', // TRE-AP
            '04', // TRE-AM
            '05', // TRE-BA
            '06', // TRE-CE
            '07', // TRE-DF
            '08', // TRE-ES
            '09', // TRE-GO
            '10', // TRE-MA
            '11', // TRE-MT
            '12', // TRE-MS
            '13', // TRE-MG
            '14', // TRE-PA
            '15', // TRE-PB
            '16', // TRE-PR
            '17', // TRE-PE
            '18', // TRE-PI
            '19', // TRE-RJ
            '20', // TRE-RN
            '21', // TRE-RS
            '22', // TRE-RO
            '23', // TRE-RR
            '24', // TRE-SC
            '25', // TRE-SE
            '26', // TRE-SP
            '27', // TRE-TO
        ]);
    }

    /**
     * Retorna o código do tribunal (TR) para os processos originários da
     * Justiça Militar da União.
     *
     * I – nos processos originários [...] e do Superior Tribunal Militar, o
     * campo (TR) deve ser preenchido com zero;
     *
     * VI – nos processos da Justiça Militar da União, as Circunscrições
     * Judiciárias Militares devem ser identificadas no campo (TR) pelos
     * números 01 a 12, observada a subdivisão vigente;
     *
     * @return string
     */
    private static function justicaMilitarUniao()
    {
        return static::randomElement([
            '00', // STM
            '01', //  1ª Circunscrição Judiciária Militar
            '02', //  2ª Circunscrição Judiciária Militar
            '03', //  3ª Circunscrição Judiciária Militar
            '04', //  4ª Circunscrição Judiciária Militar
            '05', //  5ª Circunscrição Judiciária Militar
            '06', //  6ª Circunscrição Judiciária Militar
            '07', //  7ª Circunscrição Judiciária Militar
            '08', //  8ª Circunscrição Judiciária Militar
            '09', //  9ª Circunscrição Judiciária Militar
            '10', // 10ª Circunscrição Judiciária Militar
            '11', // 11ª Circunscrição Judiciária Militar
            '12', // 12ª Circunscrição Judiciária Militar
        ]);
    }

    /**
     * Retorna o código do tribunal (TR) para os processos originários da
     * Justiça dos Estados e do Distrito Federal e Territórios.
     *
     * VII – nos processos da Justiça dos Estados e do Distrito Federal e
     * Territórios, os Tribunais de Justiça devem ser identificados no campo
     * (TR) pelos números 01 a 27, observados os Estados da Federação e o
     * Distrito Federal, em ordem alfabética;
     *
     * @return string
     */
    private static function justicaEstadual()
    {
        return static::randomElement([
            '01', // TJ-AC
            '02', // TJ-AL
            '03', // TJ-AP
            '04', // TJ-AM
            '05', // TJ-BA
            '06', // TJ-CE
            '07', // TJ-DF
            '08', // TJ-ES
            '09', // TJ-GO
            '10', // TJ-MA
            '11', // TJ-MT
            '12', // TJ-MS
            '13', // TJ-MG
            '14', // TJ-PA
            '15', // TJ-PB
            '16', // TJ-PR
            '17', // TJ-PE
            '18', // TJ-PI
            '19', // TJ-RJ
            '20', // TJ-RN
            '21', // TJ-RS
            '22', // TJ-RO
            '23', // TJ-RR
            '24', // TJ-SC
            '25', // TJ-SE
            '26', // TJ-SP
            '27', // TJ-TO
        ]);
    }

    /**
     * Retorna o código do tribunal (TR) para os processos originários da
     * Justiça Militar Estadual.
     *
     * VIII – nos processos da Justiça Militar Estadual, os Tribunais Militares
     * dos Estados de Minas Gerais, Rio Grande do Sul e São Paulo devem ser
     * identificados no campo (TR) pelos números 13, 21 e 26, respectivamente,
     * cumprida a ordem alfabética de que tratam os incisos V e VII;
     *
     * @return string
     */
    private static function justicaMilitarEstadual()
    {
        return static::randomElement([
            '13', // TJM-MG
            '21', // TJM-RS
            '26', // TJM-SP
        ]);
    }

    /**
     * Retorna a unidade de origem (OOOO) do processo. Notar que a unidade de
     * origem gerada é falsa e, pode, não existir.
     *
     * § 6º O campo (OOOO), com 4 (quatro) dígitos, identifica a unidade de
     * origem do processo, observadas as estruturas administrativas dos
     * segmentos do Poder Judiciário e as seguintes diretrizes:
     *
     * I – os tribunais devem codificar as suas respectivas unidades de origem
     * do processo no primeiro grau de jurisdição (OOOO) com utilização dos
     * números 0001 (um) a 8999 (oito mil, novecentos e noventa e nove),
     * observando-se:
     *
     * a) na Justiça Federal, as subseções judiciárias;
     * b) na Justiça do Trabalho, as varas do trabalho;
     * c) na Justiça Eleitoral, as zonas eleitorais;
     * d) na Justiça Militar da União, as auditorias militares;
     * e) na Justiça dos Estados, do Distrito Federal e dos Territórios, os
     * foros de tramitação;
     * f) na Justiça Militar Estadual, as auditorias militares.
     *
     * II - na Justiça dos Estados, do Distrito Federal e dos Territórios,
     * entende-se por foro de tramitação a sede física (fórum) onde funciona o
     * órgão judiciário responsável pela tramitação do processo, ainda que haja
     * mais de uma sede na mesma comarca e mais de um órgão judiciário na mesma
     * sede;
     *
     * III - nos processos de competência originária dos tribunais, o campo
     * (OOOO) deve ser preenchido com zero, facultada a utilização de
     * funcionalidade que oculte a sua visibilidade e/ou torne desnecessário o
     * seu preenchimento para a localização do processo;
     *
     * IV - nos processos de competência originária das turmas recursais, o
     * primeiro algarismo do campo (OOOO) deve ser preenchido com o número 9
     * (nove), facultada a utilização dos demais campos para a identificação
     * específica da turma recursal responsável pela tramitação do processo;
     *
     * @return string
     */
    private static function unidadeOrigem()
    {
        return static::numerify('####');
    }

    /**
     * Calcula o dígito verificador do número do processo.
     *
     * § 2º O campo (DD), com 2 (dois) dígitos, identifica o dígito
     * verificador, cujo cálculo de verificação deve ser efetuado pela
     * aplicação do algoritmo Módulo 97 Base 10, conforme Norma ISO 7064:2003,
     * nos termos das instruções constantes do Anexo VIII desta Resolução.
     *
     * O cálculo dos dígitos verificadores (DD) da numeração única dos
     * processos deve ser efetuado pela aplicação do algoritmo Módulo 97 Base
     * 10, conforme Norma ISO 7064:2003, de acordo com as seguintes instruções:
     *
     * I – Todos os campos do número único dos processos devem ser considerados
     * no cálculo dos dígitos verificadores;
     *
     * II – Inicialmente, os dígitos verificadores D1 D0 devem ser deslocados
     * para o final do número do processo e receber valor zero:
     * N6 N5 N4 N3 N2 N1 N0 A3 A2 A1 A0 J2 T1 R0 O3 O2 O1 O0 01 00
     *
     * III – Os dígitos de verificação D1 D0 serão calculados pela aplicação da
     * seguinte fórmula, na qual “módulo” é a operação “resto da divisão
     * inteira”:
     * D1 D0 = 98 – (N6 N5 N4 N3 N2 N1 N0 A3 A2 A1 A0 J2 T1 R0 O3 O2 O1 O0 01
     * 00 módulo 97)
     *
     * IV - O resultado da fórmula deve ser formatado em dois dígitos,
     * incluindo o zero à esquerda, se necessário. Os dígitos resultantes são
     * os dígitos verificadores, que devem ser novamente deslocados para a
     * posição original (NNNNNNNDD.AAAA.JTR.OOOO).
     *
     * V – No caso de limitação técnica de precisão computacional que impeça a
     * aplicação da fórmula sobre a integralidade do número do processo em uma
     * única operação, pode ser realizada a sua fatoração, nos seguintes
     * termos:
     * R1 = (N6 N5 N4 N3 N2 N1 N0 módulo 97)
     * R2 = ((R1 concatenado com A3 A2 A1 A0 J2 T1 R0) módulo 97)
     * R3 = ((R2 concatenado com O3 O2 O1 O0 01 00) módulo 97)
     * D1 D0 = 98 - R3
     *
     * VI – A verificação da correção do número único do processo deve ser
     * realizada pela aplicação da seguinte fórmula, cujo resultado deve ser
     * igual a 1 (um):
     * N6 N5 N4 N3 N2 N1 N0 A3 A2 A1 A0 J2 T1 R0 O3 O2 O1 O0 D1 D0 módulo 97
     *
     * Exemplo: 05 ou 15
     *
     * @return string
     */
    private static function gerarDigitoVerificador(string $sequencial, int $ano, int $orgao, string $tribunal, string $unidade_origem)
    {
        $r1 = $sequencial % 97;
        $r2 = "{$r1}{$ano}{$orgao}{$tribunal}" % 97;
        $r3 = "{$r2}{$unidade_origem}00" % 97;
        $digito = 98 - $r3;

        return sprintf('%02d', $digito);
    }
}
