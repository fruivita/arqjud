{{--
    View Livewire para listagem da documentação da aplicação.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Documentação das rotas')">

    <x-pesquisa
        wire:key="pesquisar"
        wire:model.debounce.500ms="termo"
        :erro="$errors->first('termo')"
        com_contador/>


        <x-container>

            <x-table.model.documentacao
                :excluir="$this->excluir"
                :documentacoes="$this->documentacoes"
                :preferencias="$this->preferencias"
                :ordenacoes="$this->ordenacoes"
                com_botao_novo/>

        </x-container>

</x-page>
