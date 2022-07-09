{{--
    View livewire para criação das localidades.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Novas localidades')">

    <x-container>

        <div class="space-y-6">

            <x-form.input
                wire:key="localidade-nome"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="localidade.nome"
                wire:target="store"
                autofocus
                editavel
                :erro="$errors->first('localidade.nome')"
                icone="pin-map"
                maxlength="100"
                :placeholder="__('Nome da localidade')"
                required
                :texto="__('Localidade')"
                :title="__('Informe o nome da localidade')"
                type="text"
                com_contadorr/>


            <x-form.textarea
                wire:key="localidade-descricao"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="localidade.descricao"
                wire:target="store"
                editavel
                :erro="$errors->first('localidade.descricao')"
                icone="blockquote-left"
                maxlength="255"
                :placeholder="__('Sobre a localidade')"
                :texto="__('Descrição')"
                :title="__('Descreva a localidade')"
                com_contadorr/>


            <x-grupo-button>

                <x-feedback.inline/>


                <x-button
                    wire:click="store"
                    wire:key="btn-store"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    class="btn-acao"
                    icone="save"
                    :texto="__('Salvar')"
                    :title="__('Salvar o registro')"
                    type="button"/>

            </x-grupo-button>

        </div>

    </x-container>


    <x-container>

        <x-table.model.localidade
            :excluir="$this->excluir"
            :preferencias="$this->preferencias"
            :localidades="$this->localidades"
            :ordenacoes="$this->ordenacoes"/>

    </x-container>

</x-page>
