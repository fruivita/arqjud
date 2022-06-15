{{--
    View livewire for individual box display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Box') . ': ' . $box->numberForHumans()">

    <x-backtrace :model="$box"/>


    <x-container>

        <div class="space-y-6">

            <x-show-value
                :key="__('Number')"
                :value="$box->numberForHumans()"/>


            <x-show-value
                :key="__('Description')"
                :value="$box->description"/>


            <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                <x-show-value
                    class="md:col-span-2"
                    :key="__('Site')"
                    :value="$box->shelf->stand->room->floor->building->site->name"/>


                <x-show-value
                    class="md:col-span-2"
                    :key="__('Building')"
                    :value="$box->shelf->stand->room->floor->building->name"/>


                <x-show-value
                    :key="__('Floor')"
                    :value="$box->shelf->stand->room->floor->number"/>


                <x-show-value
                    :key="__('Room')"
                    :value="$box->shelf->stand->room->number"/>


                <x-show-value
                    :key="__('Stand')"
                    :value="$box->shelf->stand->numberForHumans()"/>


                <x-show-value
                    :key="__('Shelf')"
                    :value="$box->shelf->numberForHumans()"/>

            </div>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.box.edit', $box)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <x-table.model.volume :volumes="$volumes"/>

    </x-container>

</x-page>
