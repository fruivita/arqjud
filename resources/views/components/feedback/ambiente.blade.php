{{--
    Tarja indicativa do ambiente que está hospedando a aplicação.

    É exibida sempre que não estiver no ambiente de produção.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}

<div class="flex font-bold items-center justify-center p-3 alerta">

    <h2>

        {{ __(str()->ucfirst(\App::environment())) }}

    </h2>

</div>
