{{--
    View livewire for listing floors.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Floors')">

    <x-container>

        <x-table.index.floor
            :deleting="$deleting"
            :floors="$floors"
            withdeletebutton
            withparents/>

    </x-container>

</x-page>
