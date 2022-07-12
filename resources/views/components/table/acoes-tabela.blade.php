{{--
    Idealizado para agrupar os elementos de preferência da tabela, tais como
    colunas que devem ser exibidas, paginação, etc.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div class="text-right" x-data="{ exibir_acoes : false }">

    <button
        x-on:click="exibir_acoes = ! exibir_acoes"
        class="opacity-50 transition hover:opacity-100"
        title="{{ __('Define exibição das colunas') }}"
    >

        <x-icon name="three-dots-vertical"/>

    </button>


    <x-container
        x-show="exibir_acoes"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
    >

        <div class="space-y-3">

            <div class="gap-3 grid grid-cols-1 md:grid-cols-2">{{ $slot }}</div>


            <x-por-pagina
                wire:key="por-pagina"
                wire:model.defer="preferencias.por_pagina"
                :erro="$errors->first('preferencias.por_pagina')"/>


            <x-grupo-button>

                <x-button
                    wire:click="salvarPreferencia()"
                    wire:key="btn-salvar-preferencia"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    class="btn-acao"
                    icone="save"
                    :texto="__('Salvar')"
                    :title="__('Salvar o registro')"
                    type="button"/>


                <x-button
                    x-on:click="exibir_acoes = false"
                    class="btn-cancelar"
                    icone="x-circle"
                    :texto="__('Cancelar')"
                    :title="__('Cancelar a operação')"
                    type="button"/>

            </x-grupo-button>

        </div>

    </x-container>

</div>
