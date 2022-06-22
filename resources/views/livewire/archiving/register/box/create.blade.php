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

    <x-backtrace :model="$this->shelf" :root="true"/>


    <x-container>

        <form wire:key="form-box" wire:submit.prevent="store" method="POST">

            <div class="space-y-6">

                <div class="gap-x-3 gap-y-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4">

                    <x-show-value
                        class="md:col-span-2"
                        :key="__('Site')"
                        :value="$this->shelf->site_name"/>


                    <x-show-value
                        class="md:col-span-2"
                        :key="__('Building')"
                        :value="$this->shelf->building_name"/>


                    <x-show-value
                        :key="__('Floor')"
                        :value="$this->shelf->floor_number"/>


                    <x-show-value
                        :key="__('Room')"
                        :value="$this->shelf->room_number"/>


                    <x-show-value
                        :key="__('Stand')"
                        :value="$this->shelf->stand_for_humans"/>


                    <x-show-value
                        :key="__('Shelf')"
                        :value="$this->shelf->for_humans"/>


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
                        autofocus
                        :error="$errors->first('box.number')"
                        icon="tag"
                        min="1"
                        :placeholder="__('Only numbers')"
                        required
                        :text="__('Number')"
                        :title="__('Inform the box number')"
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
            :sorts="$this->sorts"
            withdeletebutton/>

    </x-container>

</x-page>
