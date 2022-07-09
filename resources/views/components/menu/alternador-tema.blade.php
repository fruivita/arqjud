{{--
    Botão alternador do tema de exibição: modo claro/escuro.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<li>

    <button
        x-on:click="modoEscuro = ! modoEscuro"
        x-show="modoEscuro"
        class="border-primaria-500 flex items-center outline-none pl-3 space-x-3 text-left w-full focus:border-l-4 hover:border-l-4"
        title="{{ __('Alterna entre os modos escuro/claro') }}">

        <x-icon name="brightness-high"/>


        <span>{{ __('Claro') }}</span>

    </button>


    <button
        x-on:click="modoEscuro = ! modoEscuro"
        x-show="! modoEscuro"
        class="border-primaria-500 flex items-center outline-none pl-3 space-x-3 text-left w-full focus:border-l-4 hover:border-l-4"
        title="{{ __('Alterna entre os modos escuro/claro') }}">

        <x-icon name="moon-stars"/>


        <span>{{ __('Escuro') }}</span>

    </button>

</li>
