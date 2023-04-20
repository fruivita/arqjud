<!--
    View home idealizada para usuários comuns, sem permissões administrativas
    e/ou gerenciais.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import InertiaLinkCard from '@/Shared/Inertia/InertiaLinkCard.vue';
import Clipboard from '@/Shared/Misc/Clipboard.vue';
import Tooltip from '@/Shared/Misc/Tooltip.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Paginacao from '@/Shared/Tables/Paginacao.vue';
import Preferencia from '@/Shared/Tables/Preferencia.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { perPageKey, updatePerPageKey } from '@/keys';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';
import { useLocalStorage } from '@vueuse/core';
import { merge, pickBy } from 'lodash';
import { provide, readonly, ref, watch } from 'vue';

const props = defineProps({
    solicitacoes: { type: Object },
    disponiveis: { type: Object },
});

const __ = useTranslationsStore().__;

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    processo: true,
    solicitante: true,
});

const perPage = ref(props.disponiveis.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(props.disponiveis.meta.path, pickBy(merge({ per_page: perPage.value })), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['disponiveis'],
    });
};

watch(perPage, filtrar);
</script>

<template>
    <Pagina>
        <div
            class="flex justify-center space-x-6 bg-yellow-200 p-3 font-mono text-yellow-900 dark:bg-yellow-900 dark:text-yellow-50"
        >
            <span>
                {{ __('Processos solicitados') }}

                <span
                    class="rounded-full bg-yellow-900 p-1 text-yellow-50 dark:bg-yellow-200 dark:text-yellow-900"
                >
                    {{ solicitacoes.data.solicitadas }}
                </span>
            </span>

            <span>
                {{ __('Processos entregues:') }}

                <span
                    class="rounded-full bg-yellow-900 p-1 text-yellow-50 dark:bg-yellow-200 dark:text-yellow-900"
                >
                    {{ solicitacoes.data.entregues }}
                </span>
            </span>
        </div>

        <Container class="space-y-3">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <InertiaLinkCard
                    v-if="solicitacoes.links.create"
                    :href="solicitacoes.links.create"
                    :texto="__('Nova solicitação')"
                    icone="signpost"
                />

                <InertiaLinkCard
                    v-if="solicitacoes.links.view_any"
                    :href="solicitacoes.links.view_any"
                    :texto="__('Solicitações')"
                    icone="signpost-2"
                />
            </div>

            <div class="space-y-3" v-if="disponiveis.data.length">
                <Preferencia>
                    <CheckBox
                        v-model:checked="elementosVisiveis.processo"
                        :label="__('Processo')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.solicitante"
                        :label="__('Solicitante')"
                    />
                </Preferencia>

                <Tabela>
                    <template #header>
                        <Heading
                            v-show="elementosVisiveis.processo"
                            :texto="__('Processos diponíveis para retirada')"
                        />

                        <Heading
                            v-show="elementosVisiveis.solicitante"
                            :texto="__('Solicitante')"
                        />
                    </template>

                    <template #body>
                        <template v-if="disponiveis.data.length">
                            <Row v-for="solicitacao in disponiveis.data" :key="solicitacao.id">
                                <Cell v-show="elementosVisiveis.processo">
                                    <span>{{ solicitacao.processo.numero }}</span>

                                    <Clipboard
                                        :copiavel="solicitacao.processo.numero"
                                        class="ml-1"
                                    />

                                    <Tooltip
                                        v-if="solicitacao.processo.numero_antigo"
                                        :texto="solicitacao.processo.numero_antigo"
                                        class="ml-1"
                                    />
                                </Cell>

                                <Cell v-show="elementosVisiveis.solicitante">
                                    <span>{{ solicitacao.solicitante.matricula }}</span>

                                    <Tooltip
                                        v-if="solicitacao.solicitante.nome"
                                        :texto="solicitacao.solicitante.nome"
                                        class="ml-1"
                                    />
                                </Cell>
                            </Row>
                        </template>
                    </template>
                </Tabela>

                <Paginacao v-if="disponiveis.meta.last_page > 1" :meta="disponiveis.meta" />
            </div>
        </Container>
    </Pagina>
</template>
