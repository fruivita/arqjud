<?php

namespace App\Http\Livewire\Traits;

use App\Enums\AcaoCheckbox;
use Illuminate\Support\Collection;

/**
 * Trait para ações automatizadas sobre múltiplos checkbox.
 *
 * @see https://www.php.net/manual/en/language.oop5.traits.php
 * @see https://laravel-livewire.com/docs/2.x/traits
 */
trait ComAcoesDeCheckbox
{
    /**
     * Id dos itens que devem ser selecionados, isto é, que devem ter a
     * propriedade checked do checkbox definido para true.
     *
     * @var string[]
     */
    public $selecionados = [];

    /**
     * Ações de checkbox disponíveis.
     *
     * - marcar-todos - marca todos os registros
     * - desmarcar-todos - desmarca todos os registros
     * - marcar-todos-na-pagina - marca todos os registros exibidos na página
     * - desmarcar-todos-na-pagina - desmarca todos os registros exibidos na página
     *
     * @var string
     */
    public $acao_checkbox = '';

    /**
     * Todas as linhas (checkbox ids) que devem ser marcados no carregamento
     * inicial (mount) da página.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract private function selecionarIds();

    /**
     * Todas as linhas (checkbox ids) disponíveis para seleção.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract private function todosIdsSelecionaveis();

    /**
     * Range de linhas (checkbox ids) disponíveis para seleção. Em regra, as
     * linhas atualmente exibidas na página.
     *
     * @return \Illuminate\Support\Collection
     */
    abstract private function idsAtualmenteSelecionaveis();

    /**
     * Define os checkbox que devem ser marcados quando a trait é inicializada
     * pela primeira vez.
     *
     * Executado uma única vez, imediatamente após o componente ser
     * instanciado, mas antes do método render() ser acionado. É acionado
     * apenas no carregamento inicial da página e nunca mais chamado, inclusive
     * nas atualizações do componente.
     *
     * @return void
     */
    public function mountComAcoesDeCheckbox()
    {
        $selecionar = $this->selecionarIds()->pluck('id');

        $this->selecionados = $this->converterArrayPadrao($selecionar);
    }

    /**
     * Executa uma ação.
     *
     * As ações permitidas são:
     * - marcar-todos - marca todos os registros
     * - desmarcar-todos - desmarca todos os registros
     * - marcar-todos-na-pagina - marca todos os registros exibidos na página
     * - desmarcar-todos-na-pagina - desmarca todos os registros exibidos na página
     *
     * Executado após a propriedade $acao_checkbox ser atualizada.
     *
     * @return void
     *
     * @see https://laravel-livewire.com/docs/2.x/properties#computed-properties
     */
    public function updatedAcaoCheckbox()
    {
        $this->validateOnly(
            field: 'acao_checkbox',
            rules: ['acao_checkbox' => [
                'bail',
                'nullable',
                'string',
                'in:' . AcaoCheckbox::valores()->implode(','), ]],
            attributes: ['acao_checkbox' => __('Ação')]
        );

        if (! empty($this->acao_checkbox)) {
            $this->selecionados = $this->{$this->acao_checkbox};
        }
    }

    /**
     * Retorna todos os ids dos checkbox que devem ser marcados em resposta à
     * ação solicitada pelo usuário.
     *
     * Trata-se dos ids de todos os objetos.
     *
     * @return string[]
     */
    public function getSelecionarTodosProperty()
    {
        $selecionar = $this->todosIdsSelecionaveis()->pluck('id');

        return $this->converterArrayPadrao($selecionar);
    }

    /**
     * @return array
     */
    public function getDesmarcarTodosProperty()
    {
        return [];
    }

    /**
     * Retorna todos os ids que devem ser marcadas na página em resposta à ação
     * solicitada pelo usuário.
     *
     * Trata-se do id de todos os objetos exibidos na página.
     *
     * @return string[]
     */
    public function getSelecionarTodosNaPaginaProperty()
    {
        $current = $this->idsAtualmenteSelecionaveis()->pluck('id');

        $selecionar = collect($this->selecionados)->concat($current)->unique();

        return $this->converterArrayPadrao($selecionar);
    }

    /**
     * Retorna todos os ids que devem ser desmarcadas na página em resposta à
     * ação solicitada pelo usuário.
     *
     * Trata-se do id de todos os objetos exibidos na página.
     *
     * @return string[]
     */
    public function getDesmarcarTodosNaPaginaProperty()
    {
        $current = $this->idsAtualmenteSelecionaveis()->pluck('id');

        $selecionar = collect($this->selecionados)->diff($current);

        return $this->converterArrayPadrao($selecionar);
    }

    /**
     * Converte a coleção em um array padrão para o trabalho com Livewire.
     *
     * O id numérico é convertido em string, reseta os índices e, ao final,
     * retorna o array. Essas operações são necesárias para compatibilizar
     * com o Livewire e evitar resultados inesperados na seleção dos checkbox.
     *
     * @param \Illuminate\Support\Collection $colecao
     *
     * @return string[]
     */
    private function converterArrayPadrao(Collection $colecao)
    {
        return $colecao
                ->map(fn ($id) => (string) $id)
                ->values()
                ->toArray();
    }
}
