<!--
    View para listagem (com filtros) dos prédios.

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
    predios: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.predios.meta.termo ?? '');

const { confirmarExclusao, excluir, titulo } = useExclusao();

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.predios.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    predio: true,
    andares: true,
    localidade: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.predios.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.predios.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['predios'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Prédios')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <Preferencia>
                <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />

                <CheckBox v-model:checked="elementosVisiveis.predio" :label="__('Prédio')" />

                <CheckBox v-model:checked="elementosVisiveis.andares" :label="__('Qtd andares')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.localidade"
                    :label="__('Localidade')"
                />
            </Preferencia>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.predio"
                        :ordenacao="ordenacoes.nome"
                        :texto="__('Prédio')"
                        @ordenar="(direcao) => mudarOrdenacao('nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.andares"
                        :ordenacao="ordenacoes.andares_count"
                        :texto="__('Qtd andares')"
                        @ordenar="(direcao) => mudarOrdenacao('andares_count', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.localidade"
                        :ordenacao="ordenacoes.localidade_pai_nome"
                        :texto="__('Localidade')"
                        @ordenar="(direcao) => mudarOrdenacao('localidade_pai_nome', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="predios.data.length">
                        <Row v-for="predio in predios.data" :key="predio.id">
                            <Cell v-show="elementosVisiveis.acao" class="w-10" fixo>
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="predio.links.view"
                                        :href="predio.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="predio.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                predio.links.delete,
                                                __('Exclusão do prédio :attribute', {
                                                    attribute: predio.nome,
                                                })
                                            )
                                        "
                                        especie="perigo"
                                        icone="trash"
                                    />
                                </div>
                            </Cell>

                            <Cell v-show="elementosVisiveis.predio">{{ predio.nome }}</Cell>

                            <Cell v-show="elementosVisiveis.andares">{{
                                predio.andares_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.localidade">{{
                                predio.localidade.nome
                            }}</Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell :colspan="colspan">{{ __('Nenhum registro encontrado') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>

            <Paginacao v-if="predios.meta.last_page > 1" :meta="predios.meta" />
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
