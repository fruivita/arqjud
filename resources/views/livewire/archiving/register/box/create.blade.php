{{--
    View livewire for multiple and individual creation of boxes.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('New boxes')">

    <x-container>

        <form wire:key="form-box" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-3">

                    @can(\App\Enums\Policy::CreateMany->value, \App\Models\Box::class)

                        <x-form.input
                            wire:key="box-amount-can"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="amount"
                            wire:target="store"
                            :error="$errors->first('amount')"
                            icon="collection"
                            min="1"
                            max="1000"
                            :placeholder="__('Only numbers')"
                            required
                            :text="__('Amount')"
                            :title="__('Inform the amount of boxes to create at once')"
                            type="number"/>

                    @else

                        <x-form.input
                            wire:key="box-amount-cannot"
                            wire:loading.delay.attr="disabled"
                            wire:loading.delay.class="cursor-not-allowed"
                            wire:model.defer="amount"
                            wire:target="store"
                            class="cursor-not-allowed"
                            disabled
                            :error="$errors->first('amount')"
                            icon="collection"
                            min="1"
                            max="1000"
                            :placeholder="__('Only numbers')"
                            required
                            :text="__('Amount')"
                            :title="__('Inform the amount of boxes to create at once')"
                            type="number"/>

                    @endcan


                    <x-form.input
                        wire:key="box-year"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.lazy="box.year"
                        wire:target="store"
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
                        wire:target="box.year,store"
                        :error="$errors->first('box.number')"
                        icon="tag"
                        min="1"
                        :placeholder="__('Only numbers')"
                        required
                        :text="__('Number')"
                        :title="__('Inform the box number')"
                        type="number"/>

                </div>


                <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-3">

                    <x-form.input
                        wire:key="box-stand"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="box.stand"
                        wire:target="store"
                        :error="$errors->first('box.stand')"
                        icon="bookshelf"
                        min="1"
                        max="1000"
                        :placeholder="__('Only numbers')"
                        :text="__('Stand')"
                        :title="__('Inform the stand number')"
                        type="number"/>


                    <x-form.input
                        wire:key="box-shelf"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="box.shelf"
                        wire:target="store"
                        :error="$errors->first('box.shelf')"
                        icon="list-nested"
                        min="1"
                        max="1000"
                        :placeholder="__('Only numbers')"
                        :text="__('Shelf')"
                        :title="__('Inform the shelf number')"
                        type="number"/>


                    <x-form.input
                        wire:key="box-volumes"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:model.defer="volumes"
                        wire:target="store"
                        :error="$errors->first('volumes')"
                        icon="collection"
                        min="1"
                        max="1000"
                        :placeholder="__('Only numbers')"
                        required
                        :text="__('Qty of volumes')"
                        :title="__('Inform the number of volumes of the boxes')"
                        type="number"/>

                </div>


                <x-form.textarea
                    wire:key="box-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="box.description"
                    wire:target="store"
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
                        wire:target="site_id,store"
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
                            wire:target="building_id,site_id,store"
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
                                wire:target="floor_id,building_id,site_id,store"
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
                                wire:model.defer="box.room_id"
                                wire:target="floor_id,building_id,site_id,store"
                                :error="$errors->first('box.room_id')"
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

</x-page>
