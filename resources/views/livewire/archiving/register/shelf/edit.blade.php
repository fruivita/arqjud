{{--
    View livewire for individual editing of shelves.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the shelf')">

    <x-backtrace :model="$this->shelf"/>


    <x-container>

        <form wire:key="form-shelf" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="shelf-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="shelf.number"
                    wire:target="update"
                    autofocus
                    :error="$errors->first('shelf.number')"
                    icon="list-nested"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Shelf')"
                    :title="__('Inform the shelf number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="shelf-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="shelf.description"
                    wire:target="update"
                    :error="$errors->first('shelf.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the shelf')"
                    :text="__('Description')"
                    :title="__('Describes the shelf')"
                    withcounter/>


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                    {{-- Site --}}
                    <div>

                        <x-form.select
                            wire:key="site"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model="site_id"
                            wire:target="site_id,update"
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
                    <div>

                        @if($this->site_id >= 1)

                            <x-form.select
                                wire:key="buildings-{{ $this->site_id }}"
                                wire:loading.delay.attr="disabled"
                                wire:loading.delay.class="cursor-not-allowed"
                                wire:model="building_id"
                                wire:target="building_id,site_id,update"
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

                </div>


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 sm:grid-cols-3">

                    {{-- Floor --}}
                    <div>

                        @if($this->building_id >= 1)

                            <x-form.select
                                wire:key="floors-{{ $this->building_id }}"
                                wire:loading.delay.attr="disabled"
                                wire:loading.delay.class="cursor-not-allowed"
                                wire:model="floor_id"
                                wire:target="building_id,site_id,update"
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
                                wire:target="building_id,floor_id,site_id,update"
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
                                wire:model.defer="shelf.stand_id"
                                wire:target="building_id,floor_id,room_id,site_id,update"
                                :error="$errors->first('shelf.stand_id')"
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

                </div>


                <x-button-group>

                    <x-feedback.inline/>


                    <x-button
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

        <x-table.model.box
            :boxes="$this->boxes"
            :deleting="$this->deleting"
            :parent="$this->shelf"
            :sorts="$this->sorts"
            withdeletebutton
            withnewbutton/>

    </x-container>

</x-page>
