{{--
    View livewire for individual box display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Box') . ': ' . $this->box->for_humans">

    <x-backtrace :model="$this->box"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Number')"
                :value="$this->box->for_humans"/>


            <x-show-value
                :key="__('Description')"
                :value="$this->box->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                <x-show-value
                    class="md:col-span-2"
                    :key="__('Site')"
                    :value="$this->box->site_name"/>


                <x-show-value
                    class="md:col-span-2"
                    :key="__('Building')"
                    :value="$this->box->building_name"/>


                <x-show-value
                    :key="__('Floor')"
                    :value="$this->box->floor_number"/>


                <x-show-value
                    :key="__('Room')"
                    :value="$this->box->room_number"/>


                <x-show-value
                    :key="__('Stand')"
                    :value="$this->box->stand_for_humans"/>


                <x-show-value
                    :key="__('Shelf')"
                    :value="$this->box->shelf_for_humans"/>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.box.edit', $this->box->id)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.volume
            :volumes="$this->volumes"
            :sort_column="$this->sort_column"
            :sort_direction="$this->sort_direction"/>

    </x-container>

</x-page>
