{{--
    View livewire for individual shelf display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Shelf') . ': ' . $this->shelf->for_humans">

    <x-backtrace :model="$this->shelf"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Shelf')"
                :value="$this->shelf->for_humans"/>


            <x-show-value
                :key="__('Description')"
                :value="$this->shelf->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                <x-show-value
                    :key="__('Site')"
                    :value="$this->shelf->site_name"/>


                <x-show-value
                    :key="__('Building')"
                    :value="$this->shelf->building_name"/>

            </div>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-3">

                <x-show-value
                    :key="__('Floor')"
                    :value="$this->shelf->floor_number"/>


                <x-show-value
                    :key="__('Room')"
                    :value="$this->shelf->room_number"/>


                <x-show-value
                    :key="__('Stand')"
                    :value="$this->shelf->stand_for_humans"/>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Shelf::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.shelf.edit', $this->shelf->id)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.box
            :boxes="$this->boxes"
            :parent="$this->shelf"
            :sorts="$this->sorts"
            withnewbutton/>

    </x-container>

</x-page>
