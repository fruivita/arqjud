{{--
    Componente padrão para pesquisa.

     Props:
    - erro: string com a mensagem de erro para exibição
    - com_contador: boolean se o contador de caracteres deve ser exibido

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['erro' => null, 'com_contador' => false])


<div
    @if ($com_contador) x-data="{ contador: 0, visivel: false }"@endif
    class="text-primaria-900 dark:text-secundaria-50 md:mx-auto md:w-2/4"
>

    <div class="bg-primaria-100 border-2 border-primaria-300 flex items-center pl-2 py-2 pr-6 rounded dark:bg-secundaria-800 dark:border-secundaria-600">

        <label class="p-2" for="termo">

            <x-icon name="search"/>

        </label>


        <input

            @if ($com_contador)

                x-on:blur="visivel = false"
                x-on:focus="visivel = true"
                x-on:keyup="contador = $el.value.length"
                x-ref="mensagem"

            @endif

            autocomplete="off"
            autofocus
            class="bg-primaria-100 flex-1 outline-none px-4 py-2 truncate dark:bg-secundaria-800"
            id="termo"
            maxlength="50"
            placeholder="{{ __('Termo pesquisável') }}"
            type="text"
            {{ $attributes }}/>


        {{-- eventual exibição do contador de caracteres --}}
        @if ($com_contador)

            <span
                x-show="contador && visivel"
                class="text-right text-primaria-500 text-sm whitespace-nowrap dark:text-secundaria-500"
            >

                <span x-text="contador + ' / ' + $refs.mensagem.maxLength"></span>

            </span>

        @endif

    </div>


    {{-- exibição de eventual mensagem de erro --}}
    @if ($erro) <x-erro>{{ $erro }}</x-erro> @endif

</div>
