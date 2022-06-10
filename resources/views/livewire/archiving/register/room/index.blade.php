{{--
    View livewire for listing rooms.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Rooms')">

    <x-container>

        <x-table.index.room
            :deleting="$deleting"
            :rooms="$rooms"
            withdeletebutton/>

    </x-container>

</x-page>
