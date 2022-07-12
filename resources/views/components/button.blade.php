{{--
    Botão padrão.

    Props:
    - icone: string com o nome do ícone para ser utilizado
    - icone_primeiro: booleano se o ícone do elemento deve vir antes do texto.
    - texto: string para exibição no componente

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['icone', 'icone_primeiro' => false, 'texto'])


<button {{ $attributes->merge(['class' => 'btn']) }}>

    {{-- insere o ícone antes do texto --}}
    @if ($icone_primeiro)

        <x-icon :name="$icone"/>


        <span>{{ $texto }}</span>

    {{-- insere o ícone após o texto --}}
    @else

        <span>{{ $texto }}</span>


        <x-icon :name="$icone"/>

    @endif

</button>
