{{--
    Cabeçalho ocultável da tabela (table header).

    A ocultação se dá por escolha do usuário que pode escolher quais colunas
    deseja ocultar.

    Props:
    - direção: string com a direção de ordenação da coluna
    - ordenável: boolean se a coluna é ordenável
    - exibir: se o cabeçalho deve ser exibido ou não. Útil para ocultar/exibir
    uma determinada coluna inteira.
    - pesquisa_ativa: aplica determinas classes ao cabeçalho para indicar quais
    colunas são levadas em consideração na pesquisa.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['direcao' => null, 'exibir' => true, 'ordenavel' => false, 'pesquisa_ativa' => false])


@if ($exibir === true)

    <th {{

        $attributes
            ->merge(['class' => 'p-3'])
            ->when($pesquisa_ativa, function ($collection) {
                return $collection->merge(['class' => 'shadow shadow-secundaria-500 dark:shadow-primaria-500']);
            })
            ->only(['class'])

    }}>

        @if ($ordenavel)

            <button
                {{ $attributes->except(['class']) }}
                class="px-3 rounded transition whitespace-nowrap hover:bg-primaria-300 hover:dark:bg-secundaria-500"
            >

                <span class="font-bold">{{ $slot }}</span>


                <span>

                    @if ($direcao === 'asc')

                        <x-icon class="inline" name="arrow-up-short"/>

                    @elseif ($direcao === 'desc')

                        <x-icon class="inline" name="arrow-down-short"/>

                    @else

                        <x-icon class="inline" name="dash"/>

                    @endif

                </span>

            </button>

        @else

            <span class="font-bold">{{ $slot }}</span>

        @endif

    </th>

@endif
