{{--
    View livewire for listing buildings.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Buildings')">

    <x-container>

        <x-table.index.building
            :buildings="$buildings"
            :deleting="$deleting"
            withdeletebutton
            withparents/>

    </x-container>

</x-page>
