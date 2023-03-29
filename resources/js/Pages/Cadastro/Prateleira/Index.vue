<!--
    View para listagem (com filtros) das prateleiras.

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
    prateleiras: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.prateleiras.meta.termo ?? '');

const { confirmarExclusao, excluir, titulo } = useExclusao();

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.prateleiras.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    prateleira: true,
    caixas: true,
    localidade: true,
    predio: true,
    andarNumero: true,
    andarApelido: true,
    sala: true,
    estante: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.prateleiras.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.prateleiras.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['prateleiras'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Prateleiras')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <Preferencia>
                <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.prateleira"
                    :label="__('Prateleira')"
                />

                <CheckBox v-model:checked="elementosVisiveis.caixas" :label="__('Qtd caixas')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.localidade"
                    :label="__('Localidade')"
                />

                <CheckBox v-model:checked="elementosVisiveis.predio" :label="__('Prédio')" />

                <CheckBox v-model:checked="elementosVisiveis.andarNumero" :label="__('Andar')" />

                <CheckBox v-model:checked="elementosVisiveis.andarApelido" :label="__('Apelido')" />

                <CheckBox v-model:checked="elementosVisiveis.sala" :label="__('Sala')" />

                <CheckBox v-model:checked="elementosVisiveis.estante" :label="__('Estante')" />
            </Preferencia>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.prateleira"
                        :ordenacao="ordenacoes.numero"
                        :texto="__('Prateleira')"
                        @ordenar="(direcao) => mudarOrdenacao('numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.caixas"
                        :ordenacao="ordenacoes.caixas_count"
                        :texto="__('Qtd caixas')"
                        @ordenar="(direcao) => mudarOrdenacao('caixas_count', direcao)"
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

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.sala"
                        :ordenacao="ordenacoes.sala_pai_numero"
                        :texto="__('Sala')"
                        @ordenar="(direcao) => mudarOrdenacao('sala_pai_numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.estante"
                        :ordenacao="ordenacoes.estante_pai_numero"
                        :texto="__('Estante')"
                        @ordenar="(direcao) => mudarOrdenacao('estante_pai_numero', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="prateleiras.data.length">
                        <Row v-for="prateleira in prateleiras.data" :key="prateleira.id">
                            <Cell v-show="elementosVisiveis.acao" class="w-10" fixo>
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="prateleira.links.view"
                                        :href="prateleira.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="prateleira.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                prateleira.links.delete,
                                                __('Exclusão da prateleira :attribute', {
                                                    attribute: prateleira.numero,
                                                })
                                            )
                                        "
                                        especie="perigo"
                                        icone="trash"
                                    />
                                </div>
                            </Cell>

                            <Cell v-show="elementosVisiveis.prateleira">{{
                                prateleira.numero
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.caixas">{{
                                prateleira.caixas_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.localidade">{{
                                prateleira.estante.sala.andar.predio.localidade.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.predio">{{
                                prateleira.estante.sala.andar.predio.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.andarNumero">{{
                                prateleira.estante.sala.andar.numero
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.andarApelido">{{
                                prateleira.estante.sala.andar.apelido
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.sala">{{
                                prateleira.estante.sala.numero
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.estante">{{
                                prateleira.estante.numero
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

            <Paginacao v-if="prateleiras.meta.last_page > 1" :meta="prateleiras.meta" />
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
