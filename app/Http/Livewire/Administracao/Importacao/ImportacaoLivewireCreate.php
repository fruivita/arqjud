<?php

namespace App\Http\Livewire\Administracao\Importacao;

use App\Enums\Importacao;
use App\Enums\Policy;
use App\Http\Livewire\Traits\ComFeedback;
use App\Jobs\ImportarEstruturaCorporativa;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class ImportacaoLivewireCreate extends Component
{
    use AuthorizesRequests;
    use ComFeedback;

    /**
     * Importações que serão executadas
     *
     * @var string[]
     */
    public $importacoes = [];

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'importacoes' => [
                'bail',
                'required',
                'array',
                'in:' . Importacao::valores()->implode(','),
            ],
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
            'importacoes' => __('Item'),
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
        $this->authorize(Policy::ImportacaoCreate->value);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administracao.importacao.create')->layout('layouts.app');
    }

    /**
     * Cria os jobs para importação dos dados requisitados.
     *
     * @return void
     */
    public function store()
    {
        $this->validate();

        ImportarEstruturaCorporativa::dispatchIf(
            in_array(Importacao::Corporativo->value, $this->importacoes)
        )->onQueue(Importacao::Corporativo->queue());

        $this->notificar(
            true,
            __('A importação dos dados solicitada foi escalonada para execução. Em alguns minutos, os dados estarão disponíveis.'),
            10
        );
    }
}
