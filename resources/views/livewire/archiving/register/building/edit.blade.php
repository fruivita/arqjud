{{--
    View livewire for individual editing of buildings.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the building')">

    <x-backtrace :model="$building"/>


    <x-container>

        <form wire:key="form-building" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="building-name"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="building.name"
                    wire:target="update"
                    :error="$errors->first('building.name')"
                    icon="pin-map"
                    maxlength="100"
                    :placeholder="__('Building name')"
                    required
                    :text="__('Building')"
                    :title="__('Inform the building name')"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:key="building-description"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="building.description"
                    wire:target="update"
                    :error="$errors->first('building.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the building')"
                    :text="__('Description')"
                    :title="__('Describes the building')"
                    withcounter/>


                <x-form.select
                    wire:key="building-site"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="building.site_id"
                    wire:target="update"
                    :error="$errors->first('building.site_id')"
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

        <x-table.model.floor
            :floors="$floors"
            :deleting="$deleting"
            :parent="$building"
            withdeletebutton
            withnewbutton/>

    </x-container>

</x-page>
