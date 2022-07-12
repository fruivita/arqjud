{{--
    View livewire para administração dos logs de funcionamento da aplicação.

    @see https://laravel.com/docs/blade
    @see https://tailwindcss.com/
    @see https://tailwindcss.com/docs/dark-mode
    @see https://laravel-livewire.com
    @see https://alpinejs.dev/
    @see https://icons.getbootstrap.com/
--}}


<x-page :cabecalho="__('Logs de funcionamento da aplicação')">

    <x-container>

        <div class="flex flex-col space-x-0 space-y-6 lg:flex-row lg:space-x-3 lg:space-y-0">

            <x-form.select
                wire:key="arquivos-log"
                wire:loading.delay.attr="disabled"
                wire:loading.delay.class="cursor-not-allowed"
                wire:model.lazy="arquivo_log"
                editavel
                :erro="$errors->first('arquivo_log')"
                icone="file-earmark-text"
                required
                :texto="__('Arquivo de log')"
                :title="__('Escolha o arquivo de log')"
            >

                @forelse ($this->arquivos_log ?? [] as $arquivo)

                    <option value="{{ $arquivo->getFilename() }}">

                        {{ $arquivo->getFilename() }}

                    </option>

                @empty

                    <option>{{ __('Nenhum registro encontrado') }}</option>

                @endforelse

            </x-form.select>

        </div>


        @if (
            auth()->user()->can(\App\Enums\Policy::LogDelete->value)
            || auth()->user()->can(\App\Enums\Policy::LogDownload->value)
        )

            <div class="flex flex-col mt-3 space-x-0 space-y-3 lg:flex-row lg:items-center lg:justify-end lg:space-x-3 lg:space-y-0">

                <x-feedback.inline/>


                @can (\App\Enums\Policy::LogDownload->value)

                    <x-button
                        wire:click="download"
                        wire:key="btn-download"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:target="arquivo_log,delete,download"
                        class="btn-acao"
                        icone="download"
                        :texto="__('Download')"
                        :title="__('Download do arquivo de log')"
                        type="button"/>

                @endcan


                @can (\App\Enums\Policy::LogDelete->value)

                    <x-button
                        wire:click="$toggle('exibir_modal_exclusao')"
                        wire:key="btn-delete"
                        wire:loading.delay.attr="disabled"
                        wire:loading.delay.class="cursor-not-allowed"
                        wire:target="arquivo_log,delete,download"
                        class="btn-perigo"
                        icone="trash"
                        :texto="__('Excluir')"
                        :title="__('Excluir o arquivo de log')"
                        type="button"/>

                @endcan

            </div>

        @endif

    </x-container>


    <x-container>

        <x-table.topo-tabela>

            <div></div>


            <x-table.acoes-tabela/>

        </x-table.topo-tabela>


        @forelse ($this->conteudo_arquivo ?? [] as $numero_linha => $conteudo_linha)

            @if (preg_match('/^\[\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}\]/', $conteudo_linha))

                <p class="border-t-4 break-words font-bold mt-3 pt-3 text-primaria-500"><span>{{ $numero_linha + 1 }}</span>: {{ $conteudo_linha }}</p>

            @else

                <p class="break-words">{{ $conteudo_linha }}</p>

            @endif

        @empty

            <p class="text-center p-3">{{ __('No content') }}</p>

        @endforelse

    </x-container>


    <x-links-paginacao :itens="$this->conteudo_arquivo"/>


    @can (\App\Enums\Policy::LogDelete->value)

        {{-- Modal  para confirmar a excluisão do item --}}
        <x-modal-confirmacao
            wire:click="destroy"
            wire:key="modal-exclusao-{{ $this->arquivo_log }}"
            wire:model="exibir_modal_exclusao"
            :pergunta="__('Excluir o log :attribute?', ['attribute' => $this->arquivo_log])"/>

    @endcan

</x-page>
