{{--
    Links styled as a card for display on the home page.

    Props:
    - icon: svg icon that will be displayed
    - text: item description/meaning text

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props(['icon', 'text'])


<div class="bg-primary-300 rounded shadow-lg shadow-secondary-500 dark:bg-secondary-600 dark:shadow-primary-500 hover:bg-primary-200 hover:dark:bg-secondary-500">

    <a
        class="flex flex-col items-center p-3 space-y-6"
        {{ $attributes }}
    >

        <x-icon :name="$icon" class="h-16 w-16"/>

        <span class="break-words text-center">{{ $text }}</span>

    </a>

</div>
