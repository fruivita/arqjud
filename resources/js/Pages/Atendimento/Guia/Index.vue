<!--
    View para listagem (com filtros) das guias.

    Notar que:
    - As preferências de exibição são armazenadas no cache do navegador.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { useOrdenacao } from '@/Composables/UseOrdenacao';
import { perPageKey, updatePerPageKey } from '@/keys';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import Pesquisa from '@/Shared/Forms/Pesquisa.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
import LinkButtonIcone from '@/Shared/Links/LinkButtonIcone.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import HeadingOrdenavel from '@/Shared/Tables/HeadingOrdenavel.vue';
import Paginacao from '@/Shared/Tables/Paginacao.vue';
import Preferencia from '@/Shared/Tables/Preferencia.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';
import { useLocalStorage } from '@vueuse/core';
import { map, merge, pickBy } from 'lodash';
import { computed, provide, readonly, ref, watch } from 'vue';
const props = defineProps({
    guias: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.guias.meta.termo ?? '');

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.guias.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    numero: true,
    ano: true,
    gerada_em: true,
    solicitante: true,
    remetente: true,
    recebedor: true,
    lotacao_destinataria: true,
    processos: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.guias.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.guias.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['guias'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Guias')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <Preferencia>
                <CheckBox v-model="elementosVisiveis.numero" :label="__('Número')" />

                <CheckBox v-model="elementosVisiveis.ano" :label="__('Ano')" />

                <CheckBox v-model="elementosVisiveis.gerada_em" :label="__('Gerada em')" />

                <CheckBox v-model="elementosVisiveis.solicitante" :label="__('Solicitante')" />

                <CheckBox v-model="elementosVisiveis.remetente" :label="__('Remetente')" />

                <CheckBox v-model="elementosVisiveis.recebedor" :label="__('Recebedor')" />

                <CheckBox
                    v-model="elementosVisiveis.lotacao_destinataria"
                    :label="__('Lotação destinatária')"
                />

                <CheckBox v-model="elementosVisiveis.processos" :label="__('Qtd processos')" />

                <CheckBox v-model="elementosVisiveis.acao" :label="__('Ações')" />
            </Preferencia>

            <Tabela>
                <template #header>
                    <HeadingOrdenavel
                        v-show="elementosVisiveis.numero"
                        :ordenacao="ordenacoes.numero"
                        :texto="__('Número')"
                        @ordenar="(direcao) => mudarOrdenacao('numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.ano"
                        :ordenacao="ordenacoes.ano"
                        :texto="__('Ano')"
                        @ordenar="(direcao) => mudarOrdenacao('ano', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.gerada_em"
                        :ordenacao="ordenacoes.gerada_em"
                        :texto="__('Gerada em')"
                        @ordenar="(direcao) => mudarOrdenacao('gerada_em', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.solicitante" :texto="__('Solicitante')" />

                    <Heading v-show="elementosVisiveis.remetente" :texto="__('Remetente')" />

                    <Heading v-show="elementosVisiveis.recebedor" :texto="__('Recebedor')" />

                    <Heading
                        v-show="elementosVisiveis.lotacao_destinataria"
                        :texto="__('Lotação destinatária')"
                    />

                    <Heading v-show="elementosVisiveis.processos" :texto="__('Qtd processos')" />

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="guias.data.length">
                        <Row v-for="guia in guias.data" :key="guia.id">
                            <Cell v-show="elementosVisiveis.numero">{{ guia.numero }}</Cell>

                            <Cell v-show="elementosVisiveis.ano">{{ guia.ano }}</Cell>

                            <Cell v-show="elementosVisiveis.gerada_em">{{ guia.gerada_em }}</Cell>

                            <Cell
                                v-show="elementosVisiveis.solicitante"
                                :tooltip="guia.solicitante.nome"
                            >
                                {{ guia.solicitante.username }}
                            </Cell>

                            <Cell
                                v-show="elementosVisiveis.remetente"
                                :tooltip="guia.remetente.nome"
                            >
                                {{ guia.remetente.username }}
                            </Cell>

                            <Cell
                                v-show="elementosVisiveis.recebedor"
                                :tooltip="guia.recebedor.nome"
                            >
                                {{ guia.recebedor.username }}
                            </Cell>

                            <Cell
                                v-show="elementosVisiveis.lotacao_destinataria"
                                :tooltip="guia.lotacao_destinataria.nome"
                            >
                                {{ guia.lotacao_destinataria.sigla }}
                            </Cell>

                            <Cell
                                v-show="elementosVisiveis.processos"
                                :tooltip="map(guia.processos, 'numero')"
                            >
                                {{ guia.processos.length }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
                                <div
                                    v-if="guia.links?.view || guia.links?.pdf"
                                    class="flex space-x-3"
                                >
                                    <InertiaButtonIconeLink
                                        v-if="guia.links?.view"
                                        :href="guia.links.view"
                                        icone="eye"
                                    />

                                    <LinkButtonIcone
                                        v-if="guia.links?.pdf"
                                        :href="guia.links.pdf"
                                        icone="printer"
                                        target="_blank"
                                    />
                                </div>
                            </Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell :colspan="colspan">{{ __('Nenhum registro encontrado!') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>

            <Paginacao v-if="guias.meta.last_page > 1" :meta="guias.meta" />
        </Container>
    </Pagina>
</template>
