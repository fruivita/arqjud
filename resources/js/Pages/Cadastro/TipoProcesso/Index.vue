<!--
    View para listagem (com filtros) dos tipos de processo.

    Notar que:
    - As preferências de exibição são armazenadas no cache do navegador.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
    @see https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { useExclusao } from '@/Composables/UseExclusao';
import { useOrdenacao } from '@/Composables/UseOrdenacao';
import { perPageKey, updatePerPageKey } from '@/keys';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import Pesquisa from '@/Shared/Forms/Pesquisa.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
import InertiaButtonLink from '@/Shared/Inertia/InertiaButtonLink.vue';
import ModalConfirmacao from '@/Shared/Modals/ModalConfirmacao.vue';
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
import { merge, pickBy } from 'lodash';
import { computed, provide, readonly, ref, watch } from 'vue';

const props = defineProps({
    tipos_processo: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.tipos_processo.meta.termo ?? '');

const { confirmarExclusao, excluir, titulo } = useExclusao();

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.tipos_processo.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    tipo_processo: true,
    caixas: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.tipos_processo.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.tipos_processo.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['tipos_processo'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Tipos de processo')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <div
                :class="{
                    'md:justify-between': tipos_processo.links.create,
                    'md:justify-end': !tipos_processo.links.create,
                }"
                class="flex flex-col space-y-3 md:flex-row md:items-start"
            >
                <InertiaButtonLink
                    v-if="tipos_processo.links.create"
                    :href="tipos_processo.links.create"
                    :texto="__('Novo tipo de processo')"
                    icone="plus-circle"
                />

                <Preferencia>
                    <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />

                    <CheckBox
                        v-model:checked="elementosVisiveis.tipo_processo"
                        :label="__('Tipo de processo')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.caixas"
                        :label="__('Qtd caixas')"
                    />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.tipo_processo"
                        :ordenacao="ordenacoes.nome"
                        :texto="__('Tipo de processo')"
                        @ordenar="(direcao) => mudarOrdenacao('nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.caixas"
                        :ordenacao="ordenacoes.caixas_count"
                        :texto="__('Qtd caixas')"
                        @ordenar="(direcao) => mudarOrdenacao('caixas_count', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="tipos_processo.data.length">
                        <Row v-for="tipo_processo in tipos_processo.data" :key="tipo_processo.id">
                            <Cell v-show="elementosVisiveis.acao" class="w-10" fixo>
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="tipo_processo.links.view"
                                        :href="tipo_processo.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="tipo_processo.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                tipo_processo.links.delete,
                                                __('Exclusão do tipo de processo :attribute', {
                                                    attribute: tipo_processo.nome,
                                                })
                                            )
                                        "
                                        especie="perigo"
                                        icone="trash"
                                    />
                                </div>
                            </Cell>

                            <Cell v-show="elementosVisiveis.tipo_processo">{{
                                tipo_processo.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.caixas">{{
                                tipo_processo.caixas_count
                            }}</Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell :colspan="colspan">{{ __('Nenhum registro encontrado!') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>

            <Paginacao v-if="tipos_processo.meta.last_page > 1" :meta="tipos_processo.meta" />
        </Container>
    </Pagina>

    <Teleport to="body">
        <ModalConfirmacao>
            <template #header>
                <span>{{ titulo() }}</span>
            </template>

            <template #footer>
                <ButtonText
                    :texto="__('Confirmar')"
                    @click="excluir"
                    especie="perigo"
                    icone="check-circle"
                />
            </template>
        </ModalConfirmacao>
    </Teleport>
</template>
