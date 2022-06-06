{{--
    View livewire for individual floor display.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Floor') . ': ' . $floor->number">

    <x-backtrace :model="$floor"/>


    <x-container class="space-y-6">

        <div class="flex justify-between">

            @isset($previous)

                <x-link-button
                    class="btn-do"
                    icon="chevron-double-left"
                    :href="route('archiving.register.floor.show', $previous)"
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
                    :href="route('archiving.register.floor.show', $next)"
                    :text="__('Next')"
                    :title="__('Show next record')"/>

            @else

                <div></div>

            @endisset

        </div>


        <div class="space-y-6">

            <x-show-value
                :key="__('Floor')"
                :value="$floor->number"/>


            <x-show-value
                :key="__('Description')"
                :value="$floor->description"/>


            <x-show-value
                :key="__('Site')"
                :value="$floor->building->site->name"/>


            <x-show-value
                :key="__('Building')"
                :value="$floor->building->name"/>


            <x-button-group>

                @can(\App\Enums\Policy::Update->value, \App\Models\Floor::class)

                    <x-link-button
                        class="btn-do"
                        icon="pencil-square"
                        :href="route('archiving.register.floor.edit', $floor)"
                        :text="__('Edit')"
                        :title="__('Edit the record')"/>

                @endcan


                <x-link-button
                    class="btn-do"
                    icon="layers"
                    :href="route('archiving.register.floor.index')"
                    :text="__('Floors')"
                    :title="__('Show all records')"/>

            </x-button-group>


            <div class="overflow-x-auto">

                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    class="mb-3"
                    :error="$errors->first('per_page')"/>


                <x-table wire:key="table-rooms" wire:loading.delay.class="opacity-25">

                    <x-slot name="head">

                        <x-table.heading>{{ __('Room') }}</x-table.heading>


                        <x-table.heading>{{ __('Qty of boxes') }}</x-table.heading>


                        <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                    </x-slot>


                    <x-slot name="body">

                        @forelse ( $rooms ?? [] as $room )

                            <x-table.row>

                                <x-table.cell>{{ $room->number }}</x-table.cell>


                                <x-table.cell>{{ $room->boxes_count }}</x-table.cell>


                                <x-table.cell>

                                    <x-action-button-group>

                                        @can(\App\Enums\Policy::View->value, \App\Models\Room::class)

                                            <x-link-button
                                                class="btn-do"
                                                icon="eye"
                                                :href="route('archiving.register.room.show', $room)"
                                                :text="__('Show')"
                                                :title="__('Show the record')"/>

                                        @endcan


                                        @can(\App\Enums\Policy::Update->value, \App\Models\Room::class)

                                            <x-link-button
                                                class="btn-do"
                                                icon="pencil-square"
                                                :href="route('archiving.register.room.edit', $room)"
                                                :text="__('Edit')"
                                                :title="__('Edit the record')"/>

                                        @endcan

                                    </x-action-button-group>

                                </x-table.cell>

                            </x-table.row>

                        @empty

                            <x-table.row>

                                <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

                            </x-table.row>

                        @endforelse

                    </x-slot>

                </x-table>

            </div>

        </div>

    </x-container>


    {{ $rooms->links() }}

</x-page>
