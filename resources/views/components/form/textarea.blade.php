{{--
    Textarea padrão.

    Props:
    - editavel: boolean se o elemento é editável
    - erro: string com a mensagem de erro para exibição
    - icone: string com o nome do ícone para ser utilizado
    - id: string/int id do componente
    - texto: string para exibição no componente
    - com_contador: boolean se o contador de caracteres deve ser exibido

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['editavel' => false, 'erro' => null, 'icone' => 'blockquote-left', 'id', 'texto', 'com_contador' => false])


@php $id = $id ?? md5(random_int(PHP_INT_MIN, PHP_INT_MAX)); @endphp


<div
    @if ($com_contador) x-data="{ contador: 0, visivel: false }" @endif
    class="text-left w-full"
    {{ $attributes->get('title') }}
>

    {{-- texto acima do textarea --}}
    <label class="font-bold text-lg" for="{{ $id }}">

        {{ $texto }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <div @class([
        'bg-primaria-100',
        'border-2' => $editavel,
        'border-primaria-300' => $editavel,
        'flex',
        'items-center',
        'rounded',
    ])>

        @if ($editavel)

            {{-- ícone em frente ao textarea --}}
            <label class="text-primaria-900 p-2" for="{{ $id }}">

                <x-icon :name="$icone"/>

            </label>

        @endif


        {{-- textbox propriamente dito --}}
        <textarea

            @if ($com_contador)

                x-on:blur="visivel = false"
                x-on:focus="visivel = true"
                x-on:keyup="contador = $el.value.length"
                x-ref="mensagem"

            @endif


            @disabled(! $editavel)
            id="{{ $id }}"
            name="{{ $id }}"
            rows="3"
            {{
                $attributes
                ->merge(['class' => 'flex-1 outline-none p-2 text-primaria-900 disabled:dark:bg-secundaria-800 disabled:dark:text-secundaria-50'])
                ->when($erro, function ($collection) {
                    return $collection->merge(['class' => 'invalido']);
                })
            }}
            {{ $attributes->except(['class', 'title']) }}>
        </textarea>


        {{-- eventual exibição do contador de caracteres --}}
        @if ($com_contador)

            <span
                x-show="contador && visivel"
                class="px-2 text-primaria-500 text-right text-sm whitespace-nowrap dark:text-secundaria-500"
            >

                <span x-text="contador + ' / ' + $refs.mensagem.maxLength"></span>

            </span>

        @endif

    </div>


    {{-- exibição de eventual mensagem de erro --}}
    @if ($erro) <x-erro>{{ $erro }}</x-erro> @endif

</div>
