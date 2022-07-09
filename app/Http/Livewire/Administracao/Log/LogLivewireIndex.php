<?php

namespace App\Http\Livewire\Administracao\Log;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Rules\ArquivoExiste;
use FruiVita\LineReader\Facades\LineReader;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * Componente para lidar com os arquivos de log de funcionamento da aplicação.
 *
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class LogLivewireIndex extends Component
{
    use AuthorizesRequests;
    use ComFeedback;
    use ComPaginacao;
    use ComPreferencias;

    /**
     * Preferências do usuário.
     *
     * @var array<string, mixed>
     */
    public array $preferencias = [
        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Se o modal para excluisão do item deve ser exibido.
     *
     * @var bool
     */
    public $exibir_modal_exclusao = false;

    /**
     * Item em exibição.
     *
     * @var string
     */
    public $arquivo_log;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'arquivo_log' => [
                'bail',
                'required',
                'string',
                'regex:/^laravel(-\d{4}-\d{2}-\d{2})?.log$/', // laravel-1234-12-31.log ou laravel.log
                new ArquivoExiste('log-aplicacao'),
            ],
        ];
    }

    /**
     * Atributos customizados para as query strings.
     *
     * @return array<string, mixed>
     */
    protected function queryString()
    {
        return [
            'arquivo_log' => ['except' => ''],
        ];
    }

    /**
     * Atributos customizados para os erros de validação.
     *
     * @return array<string, mixed>
     */
    protected function validationAttributes()
    {
        return [
            'arquivo_log' => __('Arquivo de log'),
        ];
    }

    /**
     * Executado em cada request, imediatamente após o componente ser
     * instanciado, mas antes de qualquer outro método do ciclo de vida ser
     * acionado.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorize(Policy::LogViewAny->value);
    }

    /**
     * Executado uma única vez, imediatamente após o componente ser
     * instanciado, mas antes do método render() ser acionado. É acionado
     * apenas no carregamento inicial da página e nunca mais chamado, inclusive
     * nas atualizações do componente.
     *
     * @return void
     */
    public function mount()
    {
        $this->defineValoresPadraoBaseadoNaQueryString();
    }

    /**
     * Computed property para listar os arquivos de log.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getArquivosLogProperty()
    {
        return collect(File::allFiles($this->storage()->path('')))
        ->sortByDesc(function (\SplFileInfo $arquivo) {
            return $arquivo->getMTime();
        })->values();
    }

    /**
     * Computed property para exibir o conteúdo do arquivo de forma paginada.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     *
     * @see https://write.corbpie.com/reading-large-files-in-php-with-splfileobject/
     */
    public function getConteudoArquivoProperty()
    {
        return $this->validador()->fails()
        ? null
        : LineReader::readPaginatedLines(
            file_path: $this->caminhoCompletoArquivo(),
            per_page: $this->preferencias['por_pagina'],
            page: $this->page
        );
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administracao.log.index')->layout('layouts.app');
    }

    /**
     * Caminho completo para o arquivo em exibição.
     *
     * @return string
     */
    private function caminhoCompletoArquivo()
    {
        $arquivo = $this->arquivos_log->first(function ($arquivo) {
            return $arquivo->getFilename() === $this->arquivo_log;
        });

        return $arquivo->getRealPath();
    }

    /**
     * Executado após a propriedade $arquivo_log ser atualizada.
     *
     * @return void
     */
    public function updatedArquivoLog()
    {
        $this->validateOnly('arquivo_log');
        $this->resetPage();
    }

    /**
     * Download do arquivo em exibição.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download()
    {
        $this->authorize(Policy::LogDownload->value);

        $this->validate();

        return $this->storage()->download(
            path: $this->arquivo_log,
            name: $this->arquivo_log,
            headers: [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => "attachment; filename={$this->arquivo_log}",
            ]
        );
    }

    /**
     * Exclui o arquivo do storage.
     *
     * @return void
     */
    public function destroy()
    {
        $this->authorize(Policy::LogDelete->value);

        $this->validate();

        $excluido = $this->storage()->delete($this->arquivo_log);

        $this->defineValoresPadraoBaseadoNaQueryString();

        $this->reset('exibir_modal_exclusao');

        $this->flashSelf($excluido);
    }

    /**
     * Storage de armazenamento dos arquivos de log.
     *
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    private function storage()
    {
        return Storage::disk('log-aplicacao');
    }

    /**
     * Define o valor dos atributos com base no valor presente na query string.
     *
     * Útil para o usuário poder digitar diretamente na internet o valor de
     * interesse ou favoritar/compartilhar a página.
     *
     * @return void
     */
    private function defineValoresPadraoBaseadoNaQueryString()
    {
        $validador = $this->validador();

        $this->arquivo_log = $validador->errors()->has('arquivo_log') || empty($this->arquivo_log)
        ? optional($this->arquivos_log->first())->getFilename()
        : $this->arquivo_log;
    }

    /**
     * Valida e retorna uma instância do validador.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validador()
    {
        return Validator::make(
            ['arquivo_log' => $this->arquivo_log],
            $this->rules()
        );
    }
}
