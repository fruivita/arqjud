{{--
    Livewire view for listing buildings.

    Props:
    - buildings: buildings that will be displayed
    - deleting: item to be deleted
    - parent: parent element of the item that will eventually be created
    - preferencias: array de preferencias do usuário
    - sorts: columns and directions used to sort
    - withdeletebutton: whether the delete button should be displayed
    - withnewbutton: whether the new button should be displayed
    - withparents: whether the parent info should be displayed

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'buildings',
    'deleting' => null,
    'parent' => null,
    'preferencias',
    'sorts' => [],
    'withdeletebutton' => false,
    'withnewbutton' => false,
    'withparents' => false
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $withnewbutton == true
            && isset($parent)
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Building::class)
        )

            <x-link-button
                class="btn-do w-full md:w-auto"
                icon="plus-circle"
                :href="route('archiving.register.building.create', $parent->id)"
                :text="__('New building')"
                :title="__('Create a new record')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-predio"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="predio"
                :text="__('Building')"
                value="predio"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-andares"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="qtd_andares"
                :text="__('Qty of floors')"
                value="qtd_andares"/>


            @if ($withparents)

                <x-form.checkbox
                    wire:key="checkbox-localidade"
                    wire:loading.delay.attr="disabled"
                    wire:loading.delay.class="cursor-not-allowed"
                    wire:model.defer="preferencias.colunas"
                    editavel
                    name="localidade"
                    :text="__('Site')"
                    value="localidade"/>

            @endif


            <x-form.checkbox
                wire:key="checkbox-acoes"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="acoes"
                :text="__('Actions')"
                value="acoes"/>

        </x-table.acoes-tabela>

    </x-table.topo-tabela>


    <div class="overflow-x-auto">

        <x-table wire:key="table-buildings" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('name')"
                    :direction="$sorts['name'] ?? null"
                    :exibir="in_array('predio', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Building') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('floors_count')"
                    :direction="$sorts['floors_count'] ?? null"
                    :exibir="in_array('qtd_andares', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Qty of floors') }}

                </x-table.heading>


                @if ($withparents)

                    <x-table.heading
                        wire:click="sortBy('sites.name')"
                        :direction="$sorts['sites.name'] ?? null"
                        :exibir="in_array('localidade', $preferencias['colunas'])"
                        sortable
                    >

                        {{ __('Site') }}

                    </x-table.heading>

                @endif


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ( $buildings ?? [] as $building )

                    <x-table.row>

                        <x-table.cell :exibir="in_array('predio', $preferencias['colunas'])">{{ $building->name }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_andares', $preferencias['colunas'])">{{ $building->floors_count }}</x-table.cell>


                        @if ($withparents)

                            <x-table.cell :exibir="in_array('localidade', $preferencias['colunas'])">{{ $building->site_name }}</x-table.cell>

                        @endif


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Building::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.building.edit', $building->id)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @if(
                                    $withdeletebutton == true
                                    && auth()->user()->can(\App\Enums\Policy::Delete->value, $building)
                                )

                                    <x-icon-button
                                        wire:click="setToDelete({{ $building->id }})"
                                        wire:key="btn-delete-{{ $building->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        :title="__('Delete the record')"
                                        type="button"/>

                                @endif

                            </x-action-button-group>

                        </x-table.cell>

                    </x-table.row>

                @empty

                    <x-table.row>

                        <x-table.cell colspan="{{ count($preferencias['colunas']) }}">{{ __('No record found') }}</x-table.cell>

                    </x-table.row>

                @endforelse

            </x-slot>

        </x-table>

    </div>


    <x-links-paginacao :itens="$buildings"/>

</div>


@if(
    isset($deleting->id)
    && auth()->user()->can(\App\Enums\Policy::Delete->value, $deleting)
)

    {{-- Modal to confirm the deletion --}}
    <x-confirmation-modal
        wire:click="destroy"
        wire:key="deleting-modal-{{ $deleting->id }}"
        wire:model="show_delete_modal"
        :question="__('Delete building :attribute?', ['attribute' => $deleting->name])"/>

@endif
