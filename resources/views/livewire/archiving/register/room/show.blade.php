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


    <x-container>

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

            </x-button-group>

        </div>

    </x-container>


    <x-container>

        <div class="overflow-x-auto">

            <div class="flex items-center justify-between mb-3">

                @can(\App\Enums\Policy::Create->value, \App\Models\Stand::class)

                    <x-link-button
                        class="btn-do"
                        icon="plus-circle"
                        :href="route('archiving.register.stand.create', $room)"
                        :text="__('New')"
                        :title="__('Create a new record')"/>

                @else

                    <div></div>

                @endcan


                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    :error="$errors->first('per_page')"/>

            </div>


            <x-table wire:key="table-stands" wire:loading.delay.class="opacity-25">

                <x-slot name="head">

                    <x-table.heading>{{ __('Stand') }}</x-table.heading>


                    <x-table.heading>{{ __('Qty of shelves') }}</x-table.heading>


                    <x-table.heading class="w-10">{{ __('Actions') }}</x-table.heading>

                </x-slot>


                <x-slot name="body">

                    @forelse ( $stands ?? [] as $stand )

                        <x-table.row>

                            <x-table.cell>{{ $stand->number }}</x-table.cell>


                            <x-table.cell>{{ $stand->shelves_count }}</x-table.cell>


                            <x-table.cell>

                                <x-action-button-group>

                                    @can(\App\Enums\Policy::View->value, \App\Models\Stand::class)

                                        <x-link-button
                                            class="btn-do"
                                            icon="eye"
                                            :href="route('archiving.register.stand.show', $stand)"
                                            :text="__('Show')"
                                            :title="__('Show the record')"/>

                                    @endcan


                                    @can(\App\Enums\Policy::Update->value, \App\Models\Stand::class)

                                        <x-link-button
                                            class="btn-do"
                                            icon="pencil-square"
                                            :href="route('archiving.register.stand.edit', $stand)"
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

    </x-container>


    {{ $stands->links() }}

</x-page>
