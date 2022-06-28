{{--
    Cabeçalho ocultável da tabela (table header).

    Props:
    - direction: column sort direction
    - sortable: whether the column can be sorted
    - exibir: se o cabeçalho deve ser exibido ou não. Útil para ocultar/exibir
    uma determinada coluna inteira.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['direction' => null, 'exibir' => true, 'sortable' => null])


@if ($exibir === true)

    <th {{ $attributes->merge(['class' => 'p-3'])->only('class') }}>

        @if ($sortable)

            <button class="px-3 rounded transition whitespace-nowrap hover:bg-primary-300 hover:dark:bg-secondary-500" {{ $attributes->except('class') }}>

                <span>{{ $slot }}</span>


                <span>

                    @if ($direction === 'asc')

                        <x-icon class="inline" name="arrow-up-short"/>

                    @elseif($direction === 'desc')

                        <x-icon class="inline" name="arrow-down-short"/>

                    @else

                        <x-icon class="inline" name="dash"/>

                    @endif

                </span>

            </button>

        @else

            <span>{{ $slot }}</span>

        @endif

    </th>

@endif
