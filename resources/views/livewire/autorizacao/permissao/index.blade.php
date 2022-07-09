{{--
    View livewire para listagem das permissões.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Permissões e perfis')">

    <x-pesquisa
        wire:key="pesquisar"
        wire:model.debounce.500ms="termo"
        :erro="$errors->first('termo')"
        com_contador/>


    <x-container>

        <x-table.model.permissao
            :limite="$this->limite"
            :preferencias="$this->preferencias"
            :permissoes="$this->permissoes"
            :ordenacoes="$this->ordenacoes"
            :pesquisa_ativa="$this->termo ? true : false"/>

    </x-container>

</x-page>
