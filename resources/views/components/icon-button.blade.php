{{--
    Default button with icon only, that is, without text.

    Props:
    - icon: svg icon that will be displayed

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon'])


<button
    {{ $attributes->merge(['class' => 'btn']) }}
    {{ $attributes->except('class') }}
>

    <x-icon :name="$icon"/>

</button>
