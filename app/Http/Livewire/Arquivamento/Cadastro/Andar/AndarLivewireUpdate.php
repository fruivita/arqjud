<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Andar;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Andar;
use App\Models\Localidade;
use App\Models\Predio;
use App\Models\Sala;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class AndarLivewireUpdate extends Component
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
            'sala',
            'qtd_estantes',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Item em edição.
     *
     * @var \App\Models\Andar
     */
    public Andar $andar;

    /**
     * Todas as localidades.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $localidades = null;

    /**
     * Id da localidade selecionada.
     *
     * @var int|null
     */
    public $localidade_id = null;

    /**
     * Prédios da localidade selecionada.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $predios = null;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'andar.numero' => [
                'bail',
                'required',
                'integer',
                'between:-100,300',
                "unique:andares,numero,{$this->andar->id},id,predio_id,{$this->andar->predio_id}",
            ],

            'andar.apelido' => [
                'bail',
                'nullable',
                'string',
                'max:100',
                "unique:andares,apelido,{$this->andar->id},id,predio_id,{$this->andar->predio_id}",
            ],

            'andar.descricao' => [
                'bail',
                'nullable',
                'string',
                'max:255',
            ],

            'localidade_id' => [
                'bail',
                'required',
                'integer',
                'exists:localidades,id',
            ],

            'andar.predio_id' => [
                'bail',
                'required',
                'integer',
                'exists:predios,id',
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
            'andar.numero' => __('Andar'),
            'andar.apelido' => __('Apelido'),
            'andar.descricao' => __('Descrição'),
            'localidade_id' => __('Localidade'),
            'andar.predio_id' => __('Prédio'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Andar::class);
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
        $this->andar = Andar::hierarquia()->findOrFail($id);

        $this->inicializarPropriedadesPais();
    }

    /**
     * Computed property para listar de modo paginado as salas baseado no id do
     * andar.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getSalasProperty()
    {
        return
        Sala::hierarquia()
            ->orderByWhen($this->ordenacoes)
            ->where('andar_id', $this->andar->id)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.arquivamento.cadastro.andar.edit')->layout('layouts.app');
    }

    /**
     * Executado após a propriedade $localidade_id is updated.
     *
     * @return void
     */
    public function updatedLocalidadeId()
    {
        $this->reset(['predios']);
        $this->andar->predio_id = null;

        $this->validateOnly('localidade_id');

        $this->predios = $this->predios();
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Andar::class);

        $this->validate();

        $salvo = $this->andar->save();

        $this->reset('modo_edicao');

        $this->flashSelf($salvo);
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\Sala $sala
     *
     * @return void
     */
    public function marcarParaExcluir(Sala $sala)
    {
        $this->confirmarExclusao($sala);
    }

    /**
     * Inicializa as propriedades/relacionamentos pai do item em edição.
     *
     * @return void
     */
    private function inicializarPropriedadesPais()
    {
        $this->localidades = Localidade::orderBy('nome', 'asc')->get();
        $this->localidade_id = $this->andar->localidade_id;

        $this->predios = $this->predios();
    }

    /**
     * Prédios da localidade selecionada.
     *
     * @return \Illuminate\Support\Collection
     */
    private function predios()
    {
        return Predio::where('localidade_id', $this->localidade_id)->orderBy('nome', 'asc')->get();
    }
}
