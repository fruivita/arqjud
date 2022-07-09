{{--
    Alternador da visibilidade do menu principal.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<button
    x-on:click="menuVisivel = ! menuVisivel"
    id="menu-alternador"
    title="{{ __('Altera a visibilidade do menu') }}"
    {{ $attributes->merge([ 'class' => 'bg-primaria-300 fixed opacity-50 p-3 dark:bg-secundaria-600 lg:hidden' ]) }}
>

    {{-- botão hamburguer --}}
    <x-icon x-show="! menuVisivel" name="list"/>


    {{-- botão X --}}
    <x-icon x-show="menuVisivel" name="x"/>

</button>
