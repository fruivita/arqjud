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

    <x-backtrace :model="$this->box"/>


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
                        autofocus
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


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                    {{-- Site --}}
                    <div class="md:col-span-2">

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


                            @forelse ($this->sites ?? [] as $site)

                                <option value="{{ $site->id }}">

                                    {{ $site->name }}

                                </option>

                            @empty

                                <option value="-1">{{ __('No record found') }}</option>

                            @endforelse

                        </x-form.select>

                    </div>


                    {{-- Building --}}
                    <div class="md:col-span-2">

                        @if($this->site_id >= 1)

                            <x-form.select
                                wire:key="buildings-{{ $this->site_id }}"
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

                                @forelse ($this->buildings ?? [] as $building)

                                    <option value="{{ $building->id }}">

                                        {{ $building->name }}

                                    </option>

                                @empty

                                    <option value="-1">{{ __('No record found') }}</option>

                                @endforelse

                            </x-form.select>

                        @endif

                    </div>


                    {{-- Floor --}}
                    <div>

                        @if($this->building_id >= 1)

                            <x-form.select
                                wire:key="floors-{{ $this->building_id }}"
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

                                @forelse ($this->floors ?? [] as $floor)

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

                        @if($this->floor_id >= 1)

                            <x-form.select
                                wire:key="rooms-{{ $this->floor_id }}"
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

                                @forelse ($this->rooms ?? [] as $room)

                                    <option value="{{ $room->id }}">

                                        {{ $room->number }}

                                    </option>

                                @empty

                                    <option value="-1">{{ __('No record found') }}</option>

                                @endforelse

                            </x-form.select>

                        @endif

                    </div>


                    {{-- Stand --}}
                    <div>

                        @if($this->room_id >= 1)

                            <x-form.select
                                wire:key="stands-{{ $this->room_id }}"
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

                                @forelse ($this->stands ?? [] as $stand)

                                    <option value="{{ $stand->id }}">

                                        {{ $stand->for_humans }}

                                    </option>

                                @empty

                                    <option value="-1">{{ __('No record found') }}</option>

                                @endforelse

                            </x-form.select>

                        @endif

                    </div>


                    {{-- Shelf --}}
                    <div>

                        @if($this->stand_id >= 1)

                            <x-form.select
                                wire:key="shelves-{{ $this->stand_id }}"
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

                                @forelse ($this->shelves ?? [] as $shelf)

                                    <option value="{{ $shelf->id }}">

                                        {{ $shelf->for_humans }}

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

    </x-container>


    <x-container>

        <x-table.model.volume
            :deleting="$this->deleting"
            :volumes="$this->volumes"
            :sort_column="$this->sort_column"
            :sort_direction="$this->sort_direction"
            withdeletebutton
            withnewbutton/>

    </x-container>

</x-page>
