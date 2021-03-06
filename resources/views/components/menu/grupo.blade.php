{{--
    Container para agrupamento dos links do menu principal.

    Props:
    - nome: string para o nome do agrupamento de links do menu

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['nome'])


<section>

    <h5 class="font-extrabold mb-3 mt-8">{{ $nome }}</h5>


    <ul class="space-y-2">{{ $slot }}</ul>

</section>
