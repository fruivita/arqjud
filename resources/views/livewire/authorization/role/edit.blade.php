{{--
    View livewire for individual editing of role.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the role')">

    <x-container>

        <form wire:key="form-role" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="role-name"
                    wire:model.defer="role.name"
                    autofocus
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('role.name')"
                    icon="award"
                    maxlength="50"
                    :placeholder="__('Role name')"
                    required
                    :text="__('Name')"
                    :title="__('Role name')"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:key="role-description"
                    wire:model.defer="role.description"
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('role.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the role')"
                    :text="__('Description')"
                    :title="__('Describes the role')"
                    withcounter/>


                <div class="overflow-x-auto">

                    <x-perpage
                        wire:key="per-page"
                        wire:model="per_page"
                        class="mb-3"
                        :error="$errors->first('per_page')"/>


                    @error('checkbox_action')

                        <x-error>{{ $message }}</x-error>

                    @enderror


                    <x-table wire:key="table-permissions" wire:loading.delay.class="opacity-25">

                        <x-slot name="head">


                            <x-table.heading class="text-left">

                                @if ($this->modo_edicao)

                                    <x-table.checkbox-action
                                        wire:key="checkbox-action"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        wire:target="per_page,update"
                                        wire:model="checkbox_action"/>

                                @endif

                            </x-table.heading>


                            <x-table.heading>{{ __('Permission') }}</x-table.heading>


                            <x-table.heading>{{ __('Description') }}</x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:key="row-select-counter">

                                <x-table.cell class="text-left" colspan="3">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($this->selected) ? count($this->selected) : 0, 'total' => $this->permissions->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $this->permissions ?? [] as $permission )

                                <x-table.row wire:key="row-{{ $permission->id }}">

                                    <x-table.cell>

                                        <x-form.checkbox
                                            wire:key="checkbox-permission-{{ $permission->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selected"
                                            :checked="$this->role->permissions->contains($permission->id)"
                                            :editavel="$this->modo_edicao"
                                            :value="$permission->id"/>

                                    </x-table.cell>


                                    <x-table.cell>{{ $permission->name }}</x-table.cell>


                                    <x-table.cell>{{ $permission->description }}</x-table.cell>

                                </x-table.row>

                            @empty

                                <x-table.row>

                                    <x-table.cell colspan="3">{{ __('No record found') }}</x-table.cell>

                                </x-table.row>

                            @endforelse

                        </x-slot>

                    </x-table>


                    @error('selected')

                        <x-error>{{ $message }}</x-error>

                    @enderror

                </div>


                @can(\App\Enums\Policy::Update->value, \App\Models\Role::class)

                    <x-button-group>

                        <x-form.edit-save-cancel :modo_edicao="$this->modo_edicao"/>

                    </x-button-group>

                @endcan

            </div>

        </form>

    </x-container>


    {{ $this->permissions->links() }}

</x-page>
