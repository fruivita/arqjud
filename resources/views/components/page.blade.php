{{--
    Página propriamente dita.

    Props:
    - cabeçalho: string com o cabeçalho da página.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props (['cabecalho'])


<article {{ $attributes->merge(['class' =>'py-3 space-y-6']) }}>

    <h1 class="font-bold text-2xl text-center">{{ $cabecalho }}</h1>


    {{ $slot }}

</article>
