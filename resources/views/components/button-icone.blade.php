{{--
    Botão padrão com ícone apenas, isto é, sem texto.

    Props:
    - icone: string com o nome do ícone para ser utilizado

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icone'])


<button
    {{ $attributes->merge(['class' => 'btn']) }}
    {{ $attributes->except('class') }}
>

    <x-icon :name="$icone"/>

</button>
