{{--
    View livewire para visualização e edição individual das localidades.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Editar a localidade')">

    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="localidade-nome"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="localidade.nome"
                wire:target="update"
                autofocus
                :editavel="$this->modo_edicao"
                :erro="$errors->first('localidade.nome')"
                icone="pin-map"
                maxlength="100"
                :placeholder="__('Nome da localidade')"
                required
                :texto="__('Localidade')"
                :title="__('Informe o nome da localidade')"
                type="text"
                com_contador/>


            <x-form.textarea
                wire:key="localidade-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="localidade.descricao"
                wire:target="update"
                :editavel="$this->modo_edicao"
                :erro="$errors->first('localidade.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a localidade')"
                :texto="__('Descrição')"
                :title="__('Descreva a localidade')"
                com_contador/>


            @can(\App\Enums\Policy::Update->value, \App\Models\Localidade::class)

                <x-grupo-button>

                    <x-form.button-editar-salvar-cancelar :modo_edicao="$this->modo_edicao"/>

                </x-grupo-button>

            @endcan

        </div>

    </x-container>


    <x-container>

        <x-table.model.predio
            :predios="$this->predios"
            :excluir="$this->excluir"
            :pai="$this->localidade"
            :preferencias="$this->preferencias"
            :ordenacoes="$this->ordenacoes"
            com_botao_excluir
            com_botao_novo/>

    </x-container>

</x-page>
