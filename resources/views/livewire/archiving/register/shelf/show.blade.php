{{--
    View livewire for individual shelf display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Shelf') . ': ' . $shelf->numberForHumans()">

    <x-backtrace :model="$shelf"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Shelf')"
                :value="$shelf->numberForHumans()"/>


            <x-show-value
                :key="__('Description')"
                :value="$shelf->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-show-value
                    :key="__('Site')"
                    :value="$shelf->stand->room->floor->building->site->name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$shelf->stand->room->floor->building->name"/>

            </div>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-3">

                <x-show-value
                    :key="__('Floor')"
                    :value="$shelf->stand->room->floor->number"/>


                <x-show-value
                    :key="__('Room')"
                    :value="$shelf->stand->room->number"/>


                <x-show-value
                    :key="__('Stand')"
                    :value="$shelf->stand->numberForHumans()"/>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Shelf::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.shelf.edit', $shelf)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.box
            :boxes="$boxes"
            :parent="$shelf"
            withnewbutton/>

    </x-container>

</x-page>
