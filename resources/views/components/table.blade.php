{{--
    Tabela padrão.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<table {{ $attributes->merge(['class' => 'text-center w-full']) }}>

    <thead class="bg-primaria-200 dark:bg-secundaria-700">

        <tr>

            {{ $head }}

        </tr>

    </thead>


    <tbody>

        {{ $body }}

    </tbody>

</table>

