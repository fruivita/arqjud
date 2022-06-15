{{--
    View livewire for listing stands.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Stands')">

    <x-container>

        <x-table.model.stand
            :deleting="$deleting"
            :stands="$stands"
            withdeletebutton
            withparents/>

    </x-container>

</x-page>
