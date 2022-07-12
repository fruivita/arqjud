{{--
    Link estilizado como um card para exibição na página home.

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


@props (['icone', 'texto'])


<div class="bg-primaria-300 rounded shadow-lg shadow-secundaria-500 dark:bg-secundaria-600 dark:shadow-primaria-500 hover:bg-primaria-200 hover:dark:bg-secundaria-500">

    <a
        class="flex flex-col items-center p-3 space-y-6"
        {{ $attributes }}
    >

        <x-icon :name="$icone" class="h-16 w-16"/>

        <span class="break-words text-center">{{ $texto }}</span>

    </a>

</div>
