<!--
    View para listagem (com filtros) das salas.

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
    salas: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.salas.meta.termo ?? '');

const { confirmarExclusao, excluir, titulo } = useExclusao();

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.salas.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    sala: true,
    estantes: true,
    localidade: true,
    predio: true,
    andarNumero: true,
    andarApelido: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.salas.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.salas.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['salas'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Salas')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <Preferencia>
                <CheckBox v-model="elementosVisiveis.sala" :label="__('Sala')" />

                <CheckBox v-model="elementosVisiveis.estantes" :label="__('Qtd estantes')" />

                <CheckBox v-model="elementosVisiveis.localidade" :label="__('Localidade')" />

                <CheckBox v-model="elementosVisiveis.predio" :label="__('Prédio')" />

                <CheckBox v-model="elementosVisiveis.andarNumero" :label="__('Andar')" />

                <CheckBox v-model="elementosVisiveis.andarApelido" :label="__('Apelido')" />

                <CheckBox v-model="elementosVisiveis.acao" :label="__('Ações')" />
            </Preferencia>

            <Tabela>
                <template #header>
                    <HeadingOrdenavel
                        v-show="elementosVisiveis.sala"
                        :ordenacao="ordenacoes.numero"
                        :texto="__('Sala')"
                        @ordenar="(direcao) => mudarOrdenacao('numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.estantes"
                        :ordenacao="ordenacoes.estantes_count"
                        :texto="__('Qtd estantes')"
                        @ordenar="(direcao) => mudarOrdenacao('estantes_count', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.localidade"
                        :ordenacao="ordenacoes.localidade_pai_nome"
                        :texto="__('Localidade')"
                        @ordenar="(direcao) => mudarOrdenacao('localidade_pai_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.predio"
                        :ordenacao="ordenacoes.predio_pai_nome"
                        :texto="__('Prédio')"
                        @ordenar="(direcao) => mudarOrdenacao('predio_pai_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.andarNumero"
                        :ordenacao="ordenacoes.andar_pai_numero"
                        :texto="__('Andar')"
                        @ordenar="(direcao) => mudarOrdenacao('andar_pai_numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.andarApelido"
                        :ordenacao="ordenacoes.andar_pai_apelido"
                        :texto="__('Apelido')"
                        @ordenar="(direcao) => mudarOrdenacao('andar_pai_apelido', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="salas.data.length">
                        <Row v-for="sala in salas.data" :key="sala.id">
                            <Cell v-show="elementosVisiveis.sala">{{ sala.numero }}</Cell>

                            <Cell v-show="elementosVisiveis.estantes">{{
                                sala.estantes_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.localidade">{{
                                sala.andar.predio.localidade.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.predio">{{
                                sala.andar.predio.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.andarNumero">{{
                                sala.andar.numero
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.andarApelido">{{
                                sala.andar.apelido
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="sala.links.view"
                                        :href="sala.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="sala.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                sala.links.delete,
                                                __('Exclusão da sala :attribute', {
                                                    attribute: sala.numero,
                                                })
                                            )
                                        "
                                        especie="perigo"
                                        icone="trash"
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

            <Paginacao v-if="salas.meta.last_page > 1" :meta="salas.meta" />
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
