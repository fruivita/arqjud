{{--
    Links estilizado como um button apenas com ícone, isto é, sem texto.

    Props:
    - icone: string com o nome do ícone para ser utilizado

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['icone'])


<a {{ $attributes->merge(['class' => 'btn']) }}>

    <x-icon :name="$icone"/>

</a>
