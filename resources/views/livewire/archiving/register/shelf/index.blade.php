{{--
    View livewire for listing shelves.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Shelves')">

    <x-container>

        <x-table.index.shelf
            :deleting="$deleting"
            :shelves="$shelves"
            withdeletebutton
            withparents/>

    </x-container>

</x-page>
