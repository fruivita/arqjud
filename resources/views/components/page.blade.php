{{--
    Default page.

    Props:
    - header: page header

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['header'])


<article
    {{ $attributes->merge(['class' =>'py-3 space-y-6']) }}
    {{ $attributes }}
>

    <h1 class="font-bold text-2xl text-center">{{ $header }}</h1>


    {{ $slot }}

</article>
