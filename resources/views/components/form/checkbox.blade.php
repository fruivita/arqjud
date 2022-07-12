{{--
    Checkbox padrão.

    Props:
    - selecionado: boolean se o checkbox deve ser selecionado
    - editavel: boolean se o elemento é editável
    - texto: string para exibição no componente

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['selecionado' => false, 'editavel' => false, 'texto' => null])


<label class="flex items-center">

    <input
        @checked ($selecionado)
        @class ([
            'accent-primaria-500',
            'cursor-not-allowed' => ! $editavel,
            'h-5',
            'mr-2',
            'w-5'
        ])
        @disabled (! $editavel)
        type="checkbox"
        {{ $attributes->except(['class']) }}/>


    @isset ($texto)

        <span {{ $attributes->merge(['class' => 'select-none'])->only(['class']) }}>{{ $texto }}</span>

    @endisset

</label>
