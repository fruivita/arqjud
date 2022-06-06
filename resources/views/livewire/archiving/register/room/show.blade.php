{{--
    View livewire for individual room display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Room') . ': ' . $room->number">

    <x-backtrace :model="$room"/>


    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    :href="route('archiving.register.room.show', $previous)"
                    prepend="true"
                    :text="__('Previous')"
                    :title="__('Show previous record')"/>

            @else

              <div></div>

            @endisset


            @isset($next)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-right"
                    :href="route('archiving.register.room.show', $next)"
                    :text="__('Next')"
                    :title="__('Show next record')"/>

            @else

                <div></div>

            @endisset

        </div>


        <div class="space-y-6">

            <x-show-value
                :key="__('Room')"
                :value="$room->number"/>


            <x-show-value
                :key="__('Description')"
                :value="$room->description"/>


            <x-show-value
                :key="__('Site')"
                :value="$room->floor->building->site->name"/>


            <x-show-value
                :key="__('Building')"
                :value="$room->floor->building->name"/>


            <x-show-value
                :key="__('Floor')"
                :value="$room->floor->number"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Room::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.room.edit', $room)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan


                <x-link-button
                    class="btn-do"
                    icon="door-closed"
                    :href="route('archiving.register.room.index')"
                    :text="__('Rooms')"
                    :title="__('Show all records')"/>

            </x-button-group>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-boxes" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Box') }}</x-table.heading>


                        <x-table.heading>{{ __('Year') }}</x-table.heading>


                        <x-table.heading>{{ __('Qty of volumes') }}</x-table.heading>


                        <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $boxes ?? [] as $box )

                            <x-table.row>

                                <x-table.cell>{{ $box->number }}</x-table.cell>


                                <x-table.cell>{{ $box->year }}</x-table.cell>


                                <x-table.cell>{{ $box->volumes_count }}</x-table.cell>


                                <x-table.cell>

                                    <x-action-button-group>

                                        @can(\App\Enums\Policy::View->value, \App\Models\Box::class)

                                            <x-link-button
                                                class="btn-do"
                                                icon="eye"
                                                :href="route('archiving.register.box.show', $box)"
                                                :text="__('Show')"
                                                :title="__('Show the record')"/>

                                        @endcan


                                        @can(\App\Enums\Policy::Update->value, \App\Models\Box::class)

                                            <x-link-button
                                                class="btn-do"
                                                icon="pencil-square"
                                                :href="route('archiving.register.box.edit', $box)"
                                                :text="__('Edit')"
                                                :title="__('Edit the record')"/>

                                        @endcan

                                    </x-action-button-group>

                                </x-table.cell>

                            </x-table.row>

                        @empty

                            <x-table.row>

                                <x-table.cell colspan="4">{{ __('No record found') }}</x-table.cell>

                            </x-table.row>

                        @endforelse

                    </x-slot>

                </x-table>

            </div>

        </div>

    </x-container>


    {{ $boxes->links() }}

</x-page>
