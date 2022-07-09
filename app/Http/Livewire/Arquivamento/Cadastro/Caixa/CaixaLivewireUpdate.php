<?php

namespace App\Http\Livewire\Arquivamento\Cadastro\Caixa;

use App\Enums\Policy;
use App\Http\Livewire\Traits\ComExclusao;
use App\Http\Livewire\Traits\ComFeedback;
use App\Http\Livewire\Traits\ComOrdenacao;
use App\Http\Livewire\Traits\ComPaginacao;
use App\Http\Livewire\Traits\ComPreferencias;
use App\Models\Andar;
use App\Models\Caixa;
use App\Models\Estante;
use App\Models\Localidade;
use App\Models\Prateleira;
use App\Models\Predio;
use App\Models\Sala;
use App\Models\VolumeCaixa;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

/**
 * @see https://laravel-livewire.com/docs/2.x/quickstart
 * @see https://www.magutti.com/blog/unique-validation-on-single-and-multiple-columns-in-laravel
 */
class CaixaLivewireUpdate extends Component
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
            'volume',
            'apelido',
            'acoes',
        ],

        // Quantidade de registros exibidos por página da tabela
        'por_pagina' => 10,
    ];

    /**
     * Item em edição.
     *
     * @var \App\Models\Caixa
     */
    public Caixa $caixa;

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
     * Id do prédio selecionado.
     *
     * @var int|null
     */
    public $predio_id = null;

    /**
     * Andares do prédio selecionado.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $andares = null;

    /**
     * Id do andar selecionado.
     *
     * @var int|null
     */
    public $andar_id = null;

    /**
     * Salas do andar selecionado.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $salas = null;

    /**
     * Id da sala selecionada.
     *
     * @var int|null
     */
    public $sala_id = null;

    /**
     * Estantes da sala selecionada.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $estantes = null;

    /**
     * Id da estante selecionada.
     *
     * @var int|null
     */
    public $estante_id = null;

    /**
     * Prateleiras da estante selecionada.
     *
     * @var \Illuminate\Support\Collection|null
     */
    public ?Collection $prateleiras = null;

    /**
     * Rules para validação dos inputs.
     *
     * @return array<string, mixed>
     */
    protected function rules()
    {
        return [
            'localidade_id' => [
                'bail',
                'required',
                'integer',
                'exists:localidades,id',
            ],

            'predio_id' => [
                'bail',
                'required',
                'integer',
                'exists:predios,id',
            ],

            'andar_id' => [
                'bail',
                'required',
                'integer',
                'exists:andares,id',
            ],

            'sala_id' => [
                'bail',
                'required',
                'integer',
                'exists:salas,id',
            ],

            'estante_id' => [
                'bail',
                'required',
                'integer',
                'exists:estantes,id',
            ],

            'caixa.prateleira_id' => [
                'bail',
                'required',
                'integer',
                'exists:prateleiras,id',
            ],

            'caixa.ano' => [
                'bail',
                'required',
                'integer',
                'between:1900,' . now()->format('Y'),
            ],

            'caixa.numero' => [
                'bail',
                'required',
                'integer',
                'min:1',
                "unique:caixas,numero,{$this->caixa->id},id,ano,{$this->caixa->ano}",
            ],

            'caixa.descricao' => [
                'bail',
                'nullable',
                'string',
                'max:255',
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
            'localidade_id' => __('Localidade'),
            'predio_id' => __('Prédio'),
            'andar_id' => __('Andar'),
            'sala_id' => __('Sala'),
            'estante_id' => __('Estante'),
            'caixa.prateleira_id' => __('Prateleira'),
            'caixa.ano' => __('Ano'),
            'caixa.numero' => __('Número'),
            'caixa.descricao' => __('Descrição'),
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
        $this->authorize(Policy::ViewOrUpdate->value, Caixa::class);
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
        $this->caixa = Caixa::hierarquia()->findOrFail($id);

        $this->inicializarPropriedadesPais();
    }

    /**
     * Computed property para listar de modo paginado os volumes da caixa
     * baseado no id da caixa.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getVolumesProperty()
    {
        return
        VolumeCaixa::hierarquia()
            ->orderByWhen($this->ordenacoes)
            ->where('caixa_id', $this->caixa->id)
            ->paginate($this->preferencias['por_pagina']);
    }

    /**
     * Renderiza o componente.
     *
     * @return \Illuminate\Http\Response
     */
    public function render()
    {
        return view('livewire.arquivamento.cadastro.caixa.edit')->layout('layouts.app');
    }

    /**
     * Executado após a propriedade $localidade_id ser atualizada.
     *
     * @return void
     */
    public function updatedLocalidadeId()
    {
        $this->reset([
            'predio_id', 'predios',
            'andar_id', 'andares',
            'sala_id', 'salas',
            'estante_id', 'estantes',
            'prateleiras',
        ]);
        $this->caixa->prateleira_id = null;

        $this->validateOnly('localidade_id');

        $this->predios = $this->predios();
    }

    /**
     * Executado após a propriedade $predio_id ser atualizada.
     *
     * @return void
     */
    public function updatedPredioId()
    {
        $this->reset([
            'andar_id', 'andares',
            'sala_id', 'salas',
            'estante_id', 'estantes',
            'prateleiras',
        ]);
        $this->caixa->prateleira_id = null;

        $this->validateOnly('predio_id');

        $this->andares = $this->andares();
    }

    /**
     * Executado após a propriedade $andar_id ser atualizada.
     *
     * @return void
     */
    public function updatedAndarId()
    {
        $this->reset([
            'sala_id', 'salas',
            'estante_id', 'estantes',
            'prateleiras',
        ]);
        $this->caixa->prateleira_id = null;

        $this->validateOnly('andar_id');

        $this->salas = $this->salas();
    }

    /**
     * Executado após a propriedade $sala_id ser atualizada.
     *
     * @return void
     */
    public function updatedSalaId()
    {
        $this->reset([
            'estante_id', 'estantes',
            'prateleiras',
        ]);
        $this->caixa->prateleira_id = null;

        $this->validateOnly('sala_id');

        $this->estantes = $this->estantes();
    }

    /**
     * Executado após a propriedade $estante_id ser atualizada.
     *
     * @return void
     */
    public function updatedEstanteId()
    {
        $this->reset(['prateleiras']);
        $this->caixa->prateleira_id = null;

        $this->validateOnly('estante_id');

        $this->prateleiras = $this->prateleiras();
    }

    /**
     * Atualiza o item em edição no storage.
     *
     * @return void
     */
    public function update()
    {
        abort_if($this->modo_edicao !== true, 403);

        $this->authorize(Policy::Update->value, Caixa::class);

        $this->validate();

        $salvo = $this->caixa->save();

        $this->reset('modo_edicao');

        $this->flashSelf($salvo);
    }

    /**
     * Armazena no storage o item recém criado.
     *
     * @return void
     */
    public function storeVolume()
    {
        $this->authorize(Policy::Create->value, VolumeCaixa::class);

        $proximo_volume = $this->caixa->proximoNumeroVolume();

        $this->validarVolume($proximo_volume);

        $novo_volume = new VolumeCaixa();
        $novo_volume->numero = $proximo_volume;
        $novo_volume->apelido = "Vol. {$proximo_volume}";

        $salvo = $this->caixa->volumes()->save($novo_volume)
        ? true
        : false;

        $this->notificar($salvo, (string) $novo_volume->numero);
    }

    /**
     * Marca o item para ser excluído e aciona o modal de
     * confirmação.
     *
     * @param \App\Models\VolumeCaixa $volume_caixa
     *
     * @return void
     */
    public function marcarParaExcluir(VolumeCaixa $volume_caixa)
    {
        $this->confirmarExclusao($volume_caixa);
    }

    /**
     * Inicializa as propriedades/relacionamentos pai do item em edição.
     *
     * @return void
     */
    private function inicializarPropriedadesPais()
    {
        $this->localidades = Localidade::orderBy('nome', 'asc')->get();
        $this->localidade_id = $this->caixa->localidade_id;

        $this->predios = $this->predios();
        $this->predio_id = $this->caixa->predio_id;

        $this->andares = $this->andares();
        $this->andar_id = $this->caixa->andar_id;

        $this->salas = $this->salas();
        $this->sala_id = $this->caixa->sala_id;

        $this->estantes = $this->estantes();
        $this->estante_id = $this->caixa->estante_id;

        $this->prateleiras = $this->prateleiras();
    }

    /**
     * Valida o número do volume da caixa.
     *
     * @param int $numero
     *
     * @return void
     */
    private function validarVolume(int $numero)
    {
        Validator::make(
            data: ['volume' => $numero],
            rules: ['volume' => ['required', 'integer', 'between:1,1000']],
            customAttributes: ['volume' => __('Volume')]
        )->validate();
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

    /**
     * Andares do prédio atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function andares()
    {
        return Andar::where('predio_id', $this->predio_id)->orderBy('numero', 'asc')->get();
    }

    /**
     * Salas do andar atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function salas()
    {
        return Sala::where('andar_id', $this->andar_id)->orderBy('numero', 'asc')->get();
    }

    /**
     * Estantes da sala atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function estantes()
    {
        return Estante::where('sala_id', $this->sala_id)->orderBy('numero', 'asc')->get();
    }

    /**
     * Prateleiras da estante atual.
     *
     * @return \Illuminate\Support\Collection
     */
    private function prateleiras()
    {
        return Prateleira::where('estante_id', $this->estante_id)->orderBy('numero', 'asc')->get();
    }
}
