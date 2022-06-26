{{--
    View livewire for individual editing of stands.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the stand')">

    <x-backtrace :model="$this->stand"/>


    <x-container>

        <form wire:key="form-stand" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="stand-number"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="stand.number"
                    wire:target="update"
                    autofocus
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('stand.number')"
                    icon="bookshelf"
                    min="1"
                    max="100000"
                    :placeholder="__('Only numbers')"
                    required
                    :text="__('Stand')"
                    :title="__('Inform the stand number')"
                    type="number"/>


                <x-form.textarea
                    wire:key="stand-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="stand.description"
                    wire:target="update"
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('stand.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the stand')"
                    :text="__('Description')"
                    :title="__('Describes the stand')"
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
                            :editavel="$this->modo_edicao"
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
                                :editavel="$this->modo_edicao"
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


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2">

                    {{-- Floor --}}
                    <div>

                        @if($this->building_id >= 1)

                            <x-form.select
                                wire:key="floors-{{ $this->building_id }}"
                                wire:loading.delay.attr="disabled"
                                wire:loading.delay.class="cursor-not-allowed"
                                wire:model="floor_id"
                                wire:target="building_id,site_id,update"
                                :editavel="$this->modo_edicao"
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
                                wire:model.defer="stand.room_id"
                                wire:target="building_id,floor_id,site_id,update"
                                :editavel="$this->modo_edicao"
                                :error="$errors->first('stand.room_id')"
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

                </div>


                @can(\App\Enums\Policy::Update->value, \App\Models\Stand::class)

                    <x-button-group>

                        <x-form.edit-save-cancel :modo_edicao="$this->modo_edicao"/>

                    </x-button-group>

                @endcan

            </div>

        </form>

    </x-container>


    <x-container>

        <x-table.model.shelf
            :deleting="$this->deleting"
            :parent="$this->stand"
            :shelves="$this->shelves"
            :sorts="$this->sorts"
            withdeletebutton
            withnewbutton/>

    </x-container>

</x-page>
