{{--
    View Livewire para listagem dos andares.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Andares')">

    <x-pesquisa
        wire:key="pesquisar"
        wire:model.debounce.500ms="termo"
        :erro="$errors->first('termo')"
        com_contador/>


    <x-container>

        <x-table.model.andar
            :excluir="$this->excluir"
            :andares="$this->andares"
            :preferencias="$this->preferencias"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir
            com_pais/>

    </x-container>

</x-page>
