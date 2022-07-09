{{--
    View livewire para listagem das localidades.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Localidades')">

    <x-pesquisa
        wire:key="pesquisar"
        wire:model.debounce.500ms="termo"
        :erro="$errors->first('termo')"
        com_contador/>


    <x-container>

        <x-table.model.localidade
            :excluir="$this->excluir"
            :preferencias="$this->preferencias"
            :localidades="$this->localidades"
            :ordenacoes="$this->ordenacoes"
            com_botao_novo/>

    </x-container>

</x-page>
