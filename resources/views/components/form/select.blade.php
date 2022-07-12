{{--
    Select padrão.

    Props:
    - editavel: boolean se o elemento é editável
    - erro: string com a mensagem de erro para exibição
    - icone: string com o nome do ícone para ser utilizado
    - id: string/int id do componente
    - texto: string para exibição no componente

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['editavel' => false, 'erro' => null, 'icone', 'id', 'texto'])


@php $id = $id ?? md5(random_int(PHP_INT_MIN, PHP_INT_MAX)); @endphp


<div class="text-left w-full" {{ $attributes->only(['title']) }}>

    {{-- texto acima do select --}}
    <label class="font-bold text-lg" for="{{ $id }}">

        {{ $texto }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <div @class ([
        'bg-primaria-100',
        'border-2' => $editavel,
        'border-primaria-300' => $editavel,
        'flex',
        'items-center',
        'rounded',
    ])>


        @if ($editavel)

            {{-- ícone em frente ao select --}}
            <label class="text-primaria-900 p-2" for="{{ $id }}">

                <x-icon :name="$icone"/>

            </label>

        @endif


        {{-- select propriamente dito --}}
        <select
            @disabled (! $editavel)
            id="{{ $id }}"
            name="{{ $id }}"
            {{
                $attributes
                ->merge(['class' =>'border-none flex-1 opacity-100 p-2 text-primaria-900 truncate disabled:bg-primaria-100 disabled:dark:bg-secundaria-800 disabled:dark:text-secundaria-50 focus:outline-primaria-500'])
                ->when($erro, function ($collection) {
                    return $collection->merge(['class' => 'invalido']);
                })->except(['title'])
            }}
        >

            {{ $slot }}

        </select>

    </div>


    {{-- exibição de eventual mensagem de erro --}}
    @if ($erro) <x-erro>{{ $erro }}</x-erro> @endif

</div>
