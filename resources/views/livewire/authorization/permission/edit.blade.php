{{--
    View livewire for individual editing of permissions.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :header="__('Edit the permission')">

    <x-container>

        <form wire:key="form-permission" wire:submit.prevent="update" method="POST">

            <div class="space-y-6">

                <x-form.input
                    wire:key="permission-name"
                    wire:model.defer="permission.name"
                    autofocus
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('permission.name')"
                    icon="vector-pen"
                    maxlength="50"
                    :placeholder="__('Permission name')"
                    required
                    :text="__('Name')"
                    :title="__('Inform the permission name')"
                    type="text"
                    withcounter/>


                <x-form.textarea
                    wire:key="permission-description"
                    wire:model.defer="permission.description"
                    :editavel="$this->modo_edicao"
                    :error="$errors->first('permission.description')"
                    icon="blockquote-left"
                    maxlength="255"
                    :placeholder="__('About the permission')"
                    :text="__('Description')"
                    :title="__('Describes the permission')"
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


                    <x-table wire:key="table-roles" wire:loading.delay.class="opacity-25">

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


                            <x-table.heading>{{ __('Role') }}</x-table.heading>


                            <x-table.heading>{{ __('Description') }}</x-table.heading>

                        </x-slot>


                        <x-slot name="body">

                            <x-table.row wire:key="row-select-counter">

                                <x-table.cell class="text-left" colspan="3">

                                    <p>

                                        <span class="font-bold">

                                            {{ __(':attribute records selected from :total', ['attribute' => is_array($this->selected) ? count($this->selected) : 0, 'total' => $this->roles->total()]) }}

                                        </span>

                                    </p>

                                </x-table.cell>

                            </x-table.row>


                            @forelse ( $this->roles ?? [] as $role )

                                <x-table.row wire:key="row-{{ $role->id }}">

                                    <x-table.cell>

                                        <x-form.checkbox
                                            wire:key="checkbox-role-{{ $role->id }}"
                                            wire:loading.delay.attr="disabled"
                                            wire:loading.delay.class="cursor-not-allowed"
                                            wire:model="selected"
                                            :checked="$this->permission->roles->contains($role->id)"
                                            :editavel="$this->modo_edicao"
                                            :value="$role->id"/>

                                    </x-table.cell>


                                    <x-table.cell>{{ $role->name }}</x-table.cell>


                                    <x-table.cell>{{ $role->description }}</x-table.cell>

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


                @can(\App\Enums\Policy::Update->value, \App\Models\Permission::class)

                    <x-button-group>

                        <x-form.edit-save-cancel :modo_edicao="$this->modo_edicao"/>

                    </x-button-group>

                @endcan

            </div>

        </form>

    </x-container>


    {{ $this->roles->links() }}

</x-page>
