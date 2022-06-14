{{--
    View livewire for individual editing of boxes.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the box')">

    <x-backtrace :model="$box"/>


    <x-container class="space-y-6">

        <form wire:key="form-box" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-2">

                    <x-form.input
                        wire:key="box-year"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="box.year"
                        wire:target="storeVolume,update"
                        :error="$errors->first('box.year')"
                        icon="calendar-range"
                        min="1900"
                        :max="now()->format('Y')"
                        placeholder="aaaa"
                        required
                        :text="__('Year')"
                        :title="__('Inform the year in the yyyy pattern')"
                        type="number"/>


                    <x-form.input
                        wire:key="box-number"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="box.number"
                        wire:target="box.year,storeVolume,update"
                        :error="$errors->first('box.number')"
                        icon="tag"
                        min="1"
                        :placeholder="__('Only numbers')"
                        required
                        :text="__('Number')"
                        :title="__('Inform the box number')"
                        type="number"/>

                </div>


                <x-form.textarea
                    wire:key="box-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="box.description"
                    wire:target="storeVolume,update"
                    :error="$errors->first('box.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the box')"
                    :text="__('Description')"
                    :title="__('Describes the box')"
                    withcounter/>


                {{-- Site --}}
                <div>

                    <x-form.select
                        wire:key="site"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model="site_id"
                        wire:target="site_id,storeVolume,update"
                        :error="$errors->first('site_id')"
                        icon="pin-map"
                        required
                        :text="__('Site')"
                        :title="__('Choose site')">

                        <option value="">{{ __('Select...') }}</option>


                        @forelse ($sites ?? [] as $site)

                            <option value="{{ $site->id }}">

                                {{ $site->name }}

                            </option>

                        @empty

                            <option value="-1">{{ __('No record found') }}</option>

                        @endforelse

                    </x-form.select>

                </div>


                {{-- Building --}}
                <div>

                    @if($site_id >= 1)

                        <x-form.select
                            wire:key="buildings-{{ $site_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="building_id"
                            wire:target="building_id,site_id,storeVolume,update"
                            :error="$errors->first('building_id')"
                            icon="building"
                            required
                            :text="__('Building')"
                            :title="__('Choose building')">

                            <option value="">{{ __('Select...') }}</option>

                            @forelse ($buildings ?? [] as $building)

                                <option value="{{ $building->id }}">

                                    {{ $building->name }}

                                </option>

                            @empty

                                <option value="-1">{{ __('No record found') }}</option>

                            @endforelse

                        </x-form.select>

                    @endif

                </div>


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-2">

                    {{-- Floor --}}
                    <div>

                        @if($building_id >= 1)

                            <x-form.select
                                wire:key="floors-{{ $building_id }}"
                                wire:loading.delay.attr="disabled"
                                wire:loading.delay.class="cursor-not-allowed"
                                wire:model="floor_id"
                                wire:target="building_id,floor_id,site_id,storeVolume,update"
                                class="w-full"
                                :error="$errors->first('floor_id')"
                                icon="layers"
                                required
                                :text="__('Floor')"
                                :title="__('Choose floor')">

                                <option value="">{{ __('Select...') }}</option>

                                @forelse ($floors ?? [] as $floor)

                                    <option value="{{ $floor->id }}">

                                        {{ $floor->number }}

                                    </option>

                                @empty

                                    <option value="-1">{{ __('No record found') }}</option>

                                @endforelse

                            </x-form.select>

                        @endif

                    </div>


                    {{-- Room --}}
                    <div>

                        @if($floor_id >= 1)

                            <x-form.select
                                wire:key="rooms-{{ $floor_id }}"
                                wire:loading.delay.attr="disabled"
                                wire:loading.delay.class="cursor-not-allowed"
                                wire:model="room_id"
                                wire:target="building_id,floor_id,site_id,storeVolume,update"
                                :error="$errors->first('room_id')"
                                icon="door-closed"
                                required
                                :text="__('Room')"
                                :title="__('Choose room')">

                                <option value="">{{ __('Select...') }}</option>

                                @forelse ($rooms ?? [] as $room)

                                    <option value="{{ $room->id }}">

                                        {{ $room->number }}

                                    </option>

                                @empty

                                    <option value="-1">{{ __('No record found') }}</option>

                                @endforelse

                            </x-form.select>

                        @endif

                    </div>

                </div>


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-2">

                    {{-- Stand --}}
                    <div>

                        @if($room_id >= 1)

                            <x-form.select
                                wire:key="stands-{{ $room_id }}"
                                wire:loading.delay.attr="disabled"
                                wire:loading.delay.class="cursor-not-allowed"
                                wire:model="stand_id"
                                wire:target="building_id,floor_id,room_id,site_id,storeVolume,update"
                                :error="$errors->first('stand_id')"
                                icon="bookshelf"
                                required
                                :text="__('Stand')"
                                :title="__('Choose stand')">

                                <option value="">{{ __('Select...') }}</option>

                                @forelse ($stands ?? [] as $stand)

                                    <option value="{{ $stand->id }}">

                                        {{ $stand->numberForHumans() }}

                                    </option>

                                @empty

                                    <option value="-1">{{ __('No record found') }}</option>

                                @endforelse

                            </x-form.select>

                        @endif

                    </div>


                    {{-- Shelf --}}
                    <div>

                        @if($stand_id >= 1)

                            <x-form.select
                                wire:key="shelves-{{ $stand_id }}"
                                wire:loading.delay.attr="disabled"
                                wire:loading.delay.class="cursor-not-allowed"
                                wire:model.defer="box.shelf_id"
                                wire:target="building_id,floor_id,room_id,stand_id,site_id,storeVolume,update"
                                :error="$errors->first('box.shelf_id')"
                                icon="list-nested"
                                required
                                :text="__('Shelf')"
                                :title="__('Choose shelf')">

                                <option value="">{{ __('Select...') }}</option>

                                @forelse ($shelves ?? [] as $shelf)

                                    <option value="{{ $shelf->id }}">

                                        {{ $shelf->numberForHumans() }}

                                    </option>

                                @empty

                                    <option value="-1">{{ __('No record found') }}</option>

                                @endforelse

                            </x-form.select>

                        @endif

                    </div>

                </div>


                <x-button-group>

                    <x-feedback.inline/>


                    <x-button
                        wire:key="btn-submit"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:target="building_id,floor_id,room_id,stand_id,site_id,storeVolume,update"
                        class="btn-do"
                        icon="save"
                        :text="__('Save')"
                        :title="__('Save the record')"
                        type="submit"/>

                </x-button-group>

            </div>

        </form>


        <div class="overflow-x-auto">

            <div class="flex items-center justify-between mb-3">

                @can(\App\Enums\Policy::Create->value, \App\Models\BoxVolume::class)

                    <x-button
                        wire:click="storeVolume()"
                        wire:key="btn-store-volume"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:target="building_id,floor_id,room_id,stand_id,site_id,storeVolume,update"
                        class="btn-do mr-3"
                        icon="plus-circle"
                        :text="__('New')"
                        :title="__('Create a new record')"
                        type="button"/>


                    <x-error>{{ $errors->first('volume') }}</x-error>

                @else

                    <div></div>

                @endcan


                <x-perpage
                    wire:key="per-page"
                    wire:model="per_page"
                    :error="$errors->first('per_page')"/>

            </div>


            <x-table wire:key="table-volumes" wire:loading.delay.class="opacity-25">

                <x-slot name="head">

                    <x-table.heading>{{ __('Volume') }}</x-table.heading>

                </x-slot>


                <x-slot name="body">

                    @forelse ( $volumes ?? [] as $volume )

                        <x-table.row>

                            <x-table.cell>{{ $volume->number }}</x-table.cell>

                        </x-table.row>

                    @empty

                        <x-table.row>

                            <x-table.cell colspan="1">{{ __('No record found') }}</x-table.cell>

                        </x-table.row>

                    @endforelse

                </x-slot>

            </x-table>

        </div>

    </x-container>


    {{ $volumes->links() }}

</x-page>
