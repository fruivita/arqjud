{{--
    Default application container.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<div {{ $attributes->merge(['class' =>'flex flex-col space-y-3 lg:flex-row lg:items-start lg:justify-between']) }}>

    {{ $slot }}

</div>
