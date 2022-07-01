{{--
    Livewire view for listing sites.

    Props:
    - deleting: item to be deleted
    - preferencias: array de preferencias do usuário
    - sites: sites that will be displayed
    - sorts: columns and directions used to sort
    - withnewbutton: whether the new button should be displayed

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


@props([
    'deleting',
    'preferencias',
    'sites',
    'sorts' => [],
    'withnewbutton' => false,
])


<div class="space-y-3">

    <x-table.topo-tabela>

        @if(
            $withnewbutton == true
            && auth()->user()->can(\App\Enums\Policy::Create->value, \App\Models\Site::class)
        )

            <x-link-button
                class="btn-do w-full md:w-auto"
                icon="plus-circle"
                :href="route('archiving.register.site.create')"
                :text="__('New site')"
                :title="__('Create a new record')"/>

        @else

            <div></div>

        @endif


        <x-table.acoes-tabela>

            <x-form.checkbox
                wire:key="checkbox-localidade"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="localidade"
                :text="__('Site')"
                value="localidade"/>


            <x-form.checkbox
                wire:key="checkbox-qtd-predios"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.defer="preferencias.colunas"
                editavel
                name="qtd_predios"
                :text="__('Qty of buildings')"
                value="qtd_predios"/>


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

        <x-table wire:key="table-sites" wire:loading.delay.class="opacity-25">

            <x-slot name="head">

                <x-table.heading
                    wire:click="sortBy('name')"
                    :direction="$sorts['name'] ?? null"
                    :exibir="in_array('localidade', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Site') }}

                </x-table.heading>


                <x-table.heading
                    wire:click="sortBy('buildings_count')"
                    :direction="$sorts['buildings_count'] ?? null"
                    :exibir="in_array('qtd_predios', $preferencias['colunas'])"
                    sortable
                >

                    {{ __('Qty of buildings') }}

                </x-table.heading>


                <x-table.heading
                    class="w-10"
                    :exibir="in_array('acoes', $preferencias['colunas'])"
                >

                    {{ __('Actions') }}

                </x-table.heading>

            </x-slot>


            <x-slot name="body">

                @forelse ($sites ?? [] as $site)

                    <x-table.row>

                        <x-table.cell :exibir="in_array('localidade', $preferencias['colunas'])">{{ $site->name }}</x-table.cell>


                        <x-table.cell :exibir="in_array('qtd_predios', $preferencias['colunas'])">{{ $site->buildings_count }}</x-table.cell>


                        <x-table.cell :exibir="in_array('acoes', $preferencias['colunas'])">

                            <x-action-button-group>

                                @can(\App\Enums\Policy::ViewOrUpdate->value, \App\Models\Site::class)

                                    <x-icon-link-button
                                        class="btn-do"
                                        icon="eye"
                                        :href="route('archiving.register.site.edit', $site)"
                                        :title="__('Show the record')"/>

                                @endcan


                                @can(\App\Enums\Policy::Delete->value, $site)

                                    <x-icon-button
                                        wire:click="setToDelete({{ $site->id }})"
                                        wire:key="btn-delete-{{ $site->id }}"
                                        wire:loading.delay.attr="disabled"
                                        wire:loading.delay.class="cursor-not-allowed"
                                        class="btn-danger w-full"
                                        icon="trash"
                                        :title="__('Delete the record')"
                                        type="button"/>

                                @endcan

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


    {{ $sites->links() }}

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
        :question="__('Delete site :attribute?', ['attribute' => $deleting->name])"/>

@endif
