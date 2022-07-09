<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Predio;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class PredioLivewireUpdate extends Component
{
    use AuthorizesRequests;
    use ComExclusao;
    use ComFeedback;
    use ComOrdenacao;
    use ComPaginacao;
    use ComPreferencias;

    /**
     * Se o componente deve ser renderizado no modo edição.
     *
     * @var bool
     */
    public bool $modo_edicao = false;

    /**
     * Preferências do usuário.
     *
     * @var array<string, mixed>
     */
    public array $preferencias = [
        // Nome das colunas da tabela que podem ser ocultadas
        'colunas' => [
            'andar',
            'qtd_salas',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Item em edição.
     *
     * @var \App\Models\Predio
     */
    public Predio $predio;

    /**
     * Todas as localidades.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $localidades = null;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'predio.nome' => [
                'bail',
                'required',
                'string',
                'max:100',
                "unique:predios,nome,{$this->predio->id},id,localidade_id,{$this->predio->localidade_id}",
            ],

            'predio.descricao' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'predio.localidade_id' => [
                'bail',
                'required',
                'integer',
                'exists:localidades,id',
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
            'predio.nome' => __('Nome'),
            'predio.descricao' => __('Descrição'),
            'predio.localidade_id' => __('Localidade'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Predio::class);
    }

    /**
     * Executado uma única vez, imediatamente após o componente ser
     * instanciado, mas antes do método render() ser acionado. É acionado
     * apenas no carregamento inicial da página e nunca mais chamado, inclusive
     * nas atualizações do componente.
     *
     * @param int $id id do item em edição
     *
     * @return void
     */
    public function mount(int $id)
    {
        $this->predio = Predio::hierarquia()->findOrFail($id);

        $this->localidades = Localidade::orderBy('nome', 'asc')->get();
    }

    /**
     * Computed property para listar de modo paginado os andares baseado no id
     * do prédio.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAndaresProperty()
    {
        return
        Andar::hierarquia()
            ->orderByWhen($this->ordenacoes)
            ->where('predio_id', $this->predio->id)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.arquivamento.cadastro.predio.edit')->layout('layouts.app');
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Andar $andar
     *
     * @return void
     */
    public function marcarParaExcluir(Andar $andar)
    {
        $this->confirmarExclusao($andar);
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Predio::class);

        $this->validate();

        $salvo = $this->predio->save();

        $this->reset('modo_edicao');

        $this->flashSelf($salvo);
    }
}
