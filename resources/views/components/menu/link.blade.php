{{--
    Link do menu principal.

    Props:
    - icone: string com o nome do ícone para ser utilizado
    - texto: string para exibição no componente

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icone', 'texto'])


<li>

    <a
        {{ $attributes->merge(['class' => 'border-primaria-500 flex items-center outline-none pl-3 space-x-3 focus:border-l-4 hover:border-l-4']) }}
    >

        <x-icon :name="$icone"/>


        <span>{{ $texto }}</span>

    </a>

</li>
