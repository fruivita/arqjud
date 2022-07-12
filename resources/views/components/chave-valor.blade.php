{{--
    Exibe o pair chave e valor. Útil para exibir valores de determinados campos
    de um objeto.

     Props:
    - chave: string com geralmente o nome do campo
    - valor: string/int com o valor do campo

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['chave', 'valor'])


<div {{ $attributes->merge(['class' => "bg-primaria-100 p-3 rounded dark:bg-secundaria-800"]) }}>

    <p>

        <span class="font-bold">{{ $chave }}:</span> {{ $valor }}

    </p>

</div>
