{{--
    Destinado à escolha da quantidade de registros por página que o usuário
    deseja visualizar na tabela.

    Props:
    - erro: string com a mensagem de erro para exibição

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['erro' => null])


<div
    title="{{ __('Paginações disponíveis') }}"
    {{ $attributes->merge(['class' => "space-x-3 text-right"])->only(['class']) }}
>

    <label for="por_pagina">{{ __('Paginação') }}</label>


    <select
        class="bg-primaria-300 p-1 rounded text-right dark:bg-secundaria-500"
        id="por_pagina"
        {{ $attributes->except(['class']) }}
    >

        <option value="10">10</option>


        <option value="25">25</option>


        <option value="50">50</option>


        <option value="100">100</option>

    </select>


    {{-- exibição de eventual mensagem de erro --}}
    @if ($erro) <x-erro>{{ $erro }}</x-erro> @endif

</div>
