{{--
    View livewire for individual editing of rooms.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the room')">

    <x-backtrace :model="$this->room"/>


    <x-container>

        <form wire:key="form-room" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="room-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="room.number"
                    wire:target="update"
                    autofocus
                    :error="$errors->first('room.number')"
                    icon="layers"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Room')"
                    :title="__('Inform the room number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="room-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="room.description"
                    wire:target="update"
                    :error="$errors->first('room.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the room')"
                    :text="__('Description')"
                    :title="__('Describes the room')"
                    withcounter/>


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 xl:grid-cols-2">

                    <div>

                    {{-- Site --}}
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


                {{-- Floor --}}
                <div>

                    @if($this->building_id >= 1)

                        <x-form.select
                            wire:key="floors-{{ $this->building_id }}"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="room.floor_id"
                            wire:target="building_id,site_id,update"
                            :error="$errors->first('room.floor_id')"
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

        <x-table.model.stand
            :deleting="$this->deleting"
            :parent="$this->room"
            :stands="$this->stands"
            :sorts="$this->sorts"
            withdeletebutton
            withnewbutton/>

    </x-container>

</x-page>
