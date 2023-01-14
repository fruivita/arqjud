<!--
    View para visualização da guia.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { nomeLotacao } from '@/Helpers/Lotacao';
import { nomeUsuario } from '@/Helpers/Usuario';
import { mascaraCNJ } from '@/keys';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import LinkButtonText from '@/Shared/Links/LinkButtonText.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import Clipboard from '@/Shared/Misc/Clipboard.vue';
import Preferencia from '@/Shared/Misc/Preferencia.vue';
import Tooltip from '@/Shared/Misc/Tooltip.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { usePage } from '@inertiajs/inertia-vue3';
import { useLocalStorage } from '@vueuse/core';
import { mask } from 'maska';
import { computed } from 'vue';

const props = defineProps({
    guia: { type: Object },
});

const __ = useTranslationsStore().__;

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    processos: true,
    qtd_volumes: true,
    solicitante: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));
</script>

<template>
    <Pagina :titulo="__(':attribute: Modo Visualização', { attribute: 'Guia' })">
        <Container>
            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                    <ChaveValor
                        :chave="__('Número')"
                        :valor="`${guia.data.numero}/${guia.data.ano}`"
                        icone="tag"
                    />

                    <ChaveValor
                        :chave="__('Gerada em')"
                        :valor="guia.data.gerada_em"
                        icone="calendar-event"
                    />

                    <ChaveValor
                        :chave="__('Remetente')"
                        :valor="nomeUsuario(guia.data.remetente)"
                        icone="person"
                    />

                    <ChaveValor
                        :chave="__('Recebedor')"
                        :valor="nomeUsuario(guia.data.recebedor)"
                        icone="person"
                    />

                    <ChaveValor
                        :chave="__('Destino')"
                        :valor="nomeLotacao(guia.data.destino)"
                        icone="building"
                    />
                </div>

                <Preferencia>
                    <CheckBox
                        v-model:checked="elementosVisiveis.processos"
                        :label="__('Processos')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.qtd_volumes"
                        :label="__('Volumes')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.solicitante"
                        :label="__('Solicitante')"
                    />
                </Preferencia>

                <Tabela>
                    <template #header>
                        <Heading v-show="elementosVisiveis.processos" :texto="__('Processos')" />

                        <Heading v-show="elementosVisiveis.qtd_volumes" :texto="__('Volumes')" />

                        <Heading
                            v-show="elementosVisiveis.solicitante"
                            :texto="__('Solicitante')"
                        />
                    </template>

                    <template #body>
                        <template v-if="guia.data.processos.length">
                            <Row v-for="(processo, indice) in guia.data.processos" :key="indice">
                                <Cell v-show="elementosVisiveis.processos">
                                    <span>{{ mask(processo.numero, mascaraCNJ) }}</span>

                                    <Clipboard
                                        :copiavel="mask(processo.numero, mascaraCNJ)"
                                        class="ml-1"
                                    />
                                </Cell>

                                <Cell v-show="elementosVisiveis.qtd_volumes">{{
                                    processo.qtd_volumes
                                }}</Cell>

                                <Cell v-show="elementosVisiveis.solicitante">
                                    <span>{{ processo.solicitante.matricula }}</span>

                                    <Tooltip
                                        v-if="processo.solicitante.nome"
                                        :texto="processo.solicitante.nome"
                                        class="ml-1"
                                    />
                                </Cell>
                            </Row>
                        </template>

                        <template v-else>
                            <Row>
                                <Cell :colspan="colspan">
                                    {{ __('Nenhum registro encontrado!') }}
                                </Cell>
                            </Row>
                        </template>
                    </template>
                </Tabela>

                <LinkButtonText
                    :href="guia.data.links.pdf"
                    :texto="__('Imprimir')"
                    especie="acao"
                    icone="printer"
                    target="_blank"
                />
            </div>
        </Container>
    </Pagina>
</template>
