{{--
    Modal padrão.

    Props:
    - id: string/int id do modal

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['id'])


@php $id = $id ?? md5($attributes->wire('model')) @endphp


<div
    x-data="{ exibir_modal: @entangle($attributes->wire('model')).defer }"
    x-show="exibir_modal"
    x-on:keydown.escape.window="exibir_modal = false"
    id="{{ $id }}"
    class="fixed flex inset-0 items-center justify-center text-primaria-900 z-30 dark:text-secundaria-50"
    style="display: none;"
>

    {{-- background do modal --}}
    <div
        x-show="exibir_modal"
        x-on:click="exibir_modal = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 transform transition-all"
    >

        <div class="absolute inset-0 bg-primaria-100 opacity-90 dark:bg-secundaria-900"></div>

    </div>


    {{-- modal propriamente dito --}}
    <article
        x-show="exibir_modal"
        x-on:click.away="exibir_modal = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="divide-y transform transition-all w-full z-40 lg:w-10/12"
    >

        <header class="bg-primaria-300 rounded-t-lg p-3 dark:bg-secundaria-700">

            <h2 class="font-bold text-2xl">

                {{ $titulo }}

            </h2>

        </header>


        <div class="px-3 py-6 bg-primaria-50 dark:bg-secundaria-900 lg:px-24">

            {{ $conteudo }}

        </div>


        <footer class="bg-primaria-300 flex flex-col justify-end p-3 rounded-b-lg space-x-0 space-y-3 dark:bg-secundaria-700 lg:flex-row lg:items-center lg:space-x-3 lg:space-y-0">

            {{ $rodape }}


            <x-button
                x-on:click="exibir_modal = false"
                class="btn-cancelar"
                icone="x-circle"
                :texto="__('Cancelar')"
                :title="__('Cancelar a operação')"
                type="button"/>

        </footer>

    </article>

</div>
