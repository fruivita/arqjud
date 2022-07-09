{{--
    View Livewire para listagem das caixas.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Caixas')">

    <x-pesquisa
        wire:key="pesquisar"
        wire:model.debounce.500ms="termo"
        :erro="$errors->first('termo')"
        com_contador/>


    <x-container>

        <x-table.model.caixa
            :caixas="$this->caixas"
            :excluir="$this->excluir"
            :preferencias="$this->preferencias"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir
            com_pais/>

    </x-container>

</x-page>
