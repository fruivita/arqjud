{{--
    Checkbox com título em dimesões compatíveis com outros inputs.
    Componente criado para fins puramente estéticos.

    Props:
    - editavel: boolean se o elemento é editável
    - erro: string com a mensagem de erro para exibição
    - texto: string para exibição no componente

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['editavel' => false, 'erro' => null, 'texto'])


<div class="flex flex-col">

    {{-- texto acima do input --}}
    <label class="font-bold text-lg">

        {{ $texto }}


        @if ($attributes->has('required'))

            <span class="text-red-500">*</span>

        @endif

    </label>


    <label @class ([
        'bg-primaria-100' => ! $editavel,
        'border-2' => $editavel,
        'border-primaria-300' => $editavel,
        'flex',
        'items-center',
        'justify-center',
        'rounded',
        'dark:bg-secundaria-800' => ! $editavel,
    ])>

        <input
            @class ([
                'accent-primaria-500',
                'cursor-not-allowed' => ! $editavel,
                'h-5',
                'my-3',
                'w-5'
            ])
            @disabled (! $editavel)
            type="checkbox"
            {{ $attributes }}/>

    </label>


    {{-- exibição de eventual mensagem de erro --}}
    @if ($erro) <x-erro>{{ $erro }}</x-erro> @endif

</div>
