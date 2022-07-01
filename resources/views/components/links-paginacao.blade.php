{{--
    Gera os links para paginação dos uma coleção de itens.

    Props:
    - itens: coleção de itens para gerar os links

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['itens'])


{{ $itens->onEachSide(1)->links() }}
