{{--
    Toggler to show/hide the main menu.

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

        <x-icon name="layout-three-columns"/>

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

        <form
            wire:key="form-preferencia"
            wire:submit.prevent="salvarPreferencia()"
            class="space-y-3"
            method="POST"
        >


            <div class="gap-3 grid grid-cols-1 md:grid-cols-2">{{ $slot }}</div>


            <x-perpage
                wire:key="por-pagina"
                wire:model.defer="preferencias.por_pagina"
                :error="$errors->first('preferencias.por_pagina')"/>


            <x-button-group>

                <x-button
                    class="btn-do"
                    icon="save"
                    :text="__('Save')"
                    :title="__('Save the record')"
                    type="submit"/>


                <x-button
                    x-on:click="exibir_acoes = false"
                    class="btn-cancel"
                    icon="x-circle"
                    :text="__('Cancel')"
                    :title="__('Cancel the operation')"
                    type="button"/>

            </x-button-group>

        </form>

    </x-container>

</div>
