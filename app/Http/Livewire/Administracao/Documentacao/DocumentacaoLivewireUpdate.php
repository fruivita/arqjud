<?php

namespace App\Http\Livewire\Administracao\Documentacao;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComFeedback;
use App\Models\Documentacao;
use App\Rules\RotaExiste;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 */
class DocumentacaoLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use ComFeedback;

    /**
     * Se o componente deve ser renderizado no modo edição.
     *
     * @var bool
     */
    public bool $modo_edicao = false;

    /**
     * Item em edição.
     *
     * @var \App\Models\Documentacao
     */
    public Documentacao $documentacao;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'documentacao.app_link' => [
                'bail',
                'required',
                'string',
                'max:255',
                new RotaExiste(),
                "unique:documentacoes,app_link,{$this->documentacao->id}",
            ],

            'documentacao.doc_link' => [
                'bail',
                'nullable',
                'string',
                'max:255',
                'url',
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
            'documentacao.app_link' => __('Nome da rota'),
            'documentacao.doc_link' => __('Link da documentação'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Documentacao::class);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.administracao.documentacao.edit')->layout('layouts.app');
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Documentacao::class);

        $this->validate();

        $salvo = $this->documentacao->save();

        $this->reset('modo_edicao');

        $this->flashSelf($salvo);
    }
}
