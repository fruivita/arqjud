<?php

namespace App\Services\Importador;

use App\Models\Localidade;
use App\Models\Processo;
use App\Rules\NumeroProcesso;
use App\Rules\NumeroProcessoCNJ;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;

final class ImportadorArquivoProcesso implements ImportadorArquivoProcessoInterface
{
    /**
     * Caminho completo para o arquivo com os processos que será importado.
     *
     * @var string
     */
    private $full_path;

    /**
     * Excel writer para criação do arquivo de erro.
     *
     * @var \Spatie\SimpleExcel\SimpleExcelWriter
     */
    private $writer;

    /**
     * Campos presentes em cada linha do arquivo CSV de importação. Os campos
     * estão na sequência definida no arquivo.
     *
     * @var string[]
     */
    private $campos = [
        'numero_processo',
        'numero_antigo_processo',
        'numero_processo_pai',
        'arquivado_em',
        'qtd_volumes_processo',
        'numero_caixa',
        'complemento_caixa',
        'ano_caixa',
        'volume_caixa_inicial',
        'volume_caixa_final',
        'guarda_permanente_processo',
        'nome_localidade_criadora_caixa',
        'nome_localidade',
        'nome_predio',
        'numero_andar',
        'numero_sala',
        'numero_estante',
        'numero_prateleira',
        'descricao_caixa',
    ];

    /**
     *Rules que serão aplicadas para cada campo na importação.
     *
     * @return array<string, mixed>
     */
    private function rules()
    {
        return [
            'numero_processo' => ['bail', 'required', 'string', 'regex:/^\d+$/', 'max:25', new NumeroProcessoCNJ()],
            'numero_antigo_processo' => ['bail', 'nullable', 'string', 'regex:/^\d+$/', 'max:25', new NumeroProcesso()],
            'numero_processo_pai' => ['bail', 'nullable', 'string', 'regex:/^\d+$/', 'max:25', new NumeroProcessoCNJ(), Rule::exists('processos', 'numero')],
            'arquivado_em' => ['bail', 'required', 'date_format:d-m-Y', 'after_or_equal:01-01-1900', 'before_or_equal:' . now()->format('d-m-Y')],
            'qtd_volumes_processo' => ['bail', 'required', 'integer', 'between:1,9999'],
            'numero_caixa' => ['bail', 'required', 'integer', 'min:1'],
            'complemento_caixa' => ['bail', 'nullable', 'string', 'between:1,50'],
            'ano_caixa' => ['bail', 'required', 'integer', 'between:1900,' . now()->format('Y')],
            'volume_caixa_inicial' => ['bail', 'required', 'integer', 'between:1,9999'],
            'volume_caixa_final' => ['bail', 'required', 'integer', 'gte:volume_caixa_inicial', 'max:9999'],
            'guarda_permanente_processo' => ['bail', 'required', 'in:SIM,Sim,sim,NÃO,Não,não'],
            'nome_localidade_criadora_caixa' => ['bail', 'required', 'string', 'between:1,100'],
            'nome_localidade' => ['bail', 'required', 'string', 'between:1,100'],
            'nome_predio' => ['bail', 'required', 'string', 'between:1,100'],
            'numero_andar' => ['bail', 'required', 'integer', 'between:-100,300'],
            'numero_sala' => ['bail', 'required', 'string', 'between:1,50'],
            'numero_estante' => ['bail', 'nullable', 'string', 'between:1,50'],
            'numero_prateleira' => ['bail', 'nullable', 'string', 'between:1,50'],
            'descricao_caixa' => ['bail', 'nullable', 'string', 'between:1,255'],
        ];
    }

    /**
     * Create new class instance.
     *
     * @return static
     */
    public static function make()
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function importar(string $arquivo)
    {
        $this->iniciar();
        $this->processar($arquivo);
        $this->finalizar();
    }

    /**
     * Tratativas iniciais da importação.
     *
     * @return void
     */
    private function iniciar()
    {
        Log::notice(__('Inicio da importação dos processos'));
    }

    /**
     * Executa a importação propriamente dita.
     *
     * @param  string  $arquivo nome do arquivo que será importado.
     * @return void
     */
    private function processar($arquivo)
    {
        $this->full_path = Storage::disk('processo')->path($arquivo);
        $full_path_erro = Storage::disk('processo')->path("erro-{$arquivo}");
        $this->writer = SimpleExcelWriter::create($full_path_erro);

        $this->importarProcessos();
        $this->importarRelacionamentoProcessoPai();
    }

    /**
     * Importa os processos e cria todos os relacionamentos, exceto o entre
     * processo pai e filho.
     *
     * @return void
     */
    private function importarProcessos()
    {
        SimpleExcelReader::create($this->full_path)
            ->useHeaders($this->campos)
            ->getRows()
            ->each(function (array $input) {
                // remove o processo pai, visto que ele será inserido apenas na
                // segunda passagem para garantir a sua existência.
                $validador = Validator::make(
                    Arr::except($input, ['numero_processo_pai']),
                    $this->rules()
                );

                if ($validador->fails()) {
                    $this->writer->addRow($input + ['falha' => $validador->getMessageBag()->first()]);
                } else {
                    $validados = $validador->validated();

                    if ($validados) {
                        $this->salvar($validados);
                    }
                }
            });
    }

    /**
     * Importa o relacionamento processo pai/filho.
     *
     * @return void
     */
    private function importarRelacionamentoProcessoPai()
    {
        // campos necessários para a criação do relacionamento
        $campos = ['numero_processo', 'numero_processo_pai'];
        $rules = Arr::only($this->rules(), $campos);

        SimpleExcelReader::create($this->full_path)
            ->useHeaders($this->campos)
            ->getRows()
            ->filter(fn (array $input) => !empty(Arr::get($input, 'numero_processo_pai')))
            ->each(function (array $input) use ($campos, $rules) {
                $dados = Arr::only($input, $campos);
                $validador = Validator::make($dados, $rules);

                if ($validador->fails()) {
                    $this->writer->addRow($input + ['falha' => $validador->getMessageBag()->first()]);
                } else {
                    $validados = $validador->validated();

                    if ($validados) {
                        $this->vincularProcessoPai(Arr::get($validados, 'numero_processo'), Arr::get($validados, 'numero_processo_pai'));
                    }
                }
            });
    }

    /**
     * Cria o relacionamento com o processo pai.
     *
     * @return bool
     */
    private function vincularProcessoPai(string $numero_processo, string $numero_processo_pai)
    {
        $processo_pai = Processo::firstWhere('numero', $numero_processo_pai);
        $processo = Processo::firstWhere('numero', $numero_processo);

        $processo->processoPai()->associate($processo_pai);

        return $processo->save();
    }

    /**
     * Faz a persistência dos dados validados.
     *
     * @return bool
     */
    private function salvar(array $validados)
    {
        try {
            $localidade = Localidade::firstOrCreate(['nome' => Arr::get($validados, 'nome_localidade')]);
            $localidade_criadora = Localidade::firstOrCreate(['nome' => Arr::get($validados, 'nome_localidade_criadora_caixa')]);
            $predio = $localidade->predios()->firstOrCreate(['nome' => Arr::get($validados, 'nome_predio')]);
            $andar = $predio->andares()->firstOrCreate(['numero' => Arr::get($validados, 'numero_andar')]);
            $sala = $andar->salas()->firstOrCreate(['numero' => Arr::get($validados, 'numero_sala')]);
            $estante = $sala->estantes()->firstOrCreate([
                'numero' => Arr::get($validados, 'numero_estante') ?: '0',
            ]);
            $prateleira = $estante->prateleiras()->firstOrCreate([
                'numero' => Arr::get($validados, 'numero_prateleira') ?: '0',
            ]);
            $caixa = $prateleira->caixas()->firstOrCreate(
                [
                    'numero' => Arr::get($validados, 'numero_caixa'),
                    'ano' => Arr::get($validados, 'ano_caixa'),
                    'guarda_permanente' => str(Arr::get($validados, 'guarda_permanente_processo'))->lower()->exactly('sim'),
                    'complemento' => Arr::get($validados, 'complemento_caixa') ?: null,
                    'localidade_criadora_id' => $localidade_criadora->id,
                ],
                ['descricao' => Arr::get($validados, 'descricao_caixa') ?: null]
            );
            $caixa->processos()->firstOrCreate(
                ['numero' => Arr::get($validados, 'numero_processo')],
                [
                    'guarda_permanente' => str(Arr::get($validados, 'guarda_permanente_processo'))->lower()->exactly('sim'),
                    'arquivado_em' => Carbon::createFromFormat('d-m-Y', Arr::get($validados, 'arquivado_em')),
                    'qtd_volumes' => Arr::get($validados, 'qtd_volumes_processo'),
                    'vol_caixa_inicial' => Arr::get($validados, 'volume_caixa_inicial'),
                    'vol_caixa_final' => Arr::get($validados, 'volume_caixa_final'),
                    'numero_antigo' => Arr::get($validados, 'numero_antigo_processo') ?: null,
                ]
            );

            return true;
        } catch (\Throwable $exception) {
            Log::critical(
                __('Falha ao importar os processos'),
                [
                    'input' => $validados,
                    'exception' => $exception,
                ]
            );

            return false;
        }
    }

    /**
     * Tratativas pós importação.
     *
     * @return void
     */
    private function finalizar()
    {
        Log::notice(__('Final da importação dos processos'));
    }
}
