<!--
    View para listagem (com filtros) das caixas.

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
import { gp, numeroCaixa } from '@/Helpers/Caixa';
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
    caixas: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.caixas.meta.termo ?? '');

const { confirmarExclusao, excluir, titulo } = useExclusao();

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.caixas.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    caixa: true,
    ano: true,
    guardaPermanente: true,
    complemento: true,
    processos: true,
    localidadeCriadora: true,
    tipoProcesso: true,
    localidade: true,
    predio: true,
    andarNumero: true,
    andarApelido: true,
    sala: true,
    estante: true,
    prateleira: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.caixas.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.caixas.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['caixas'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Caixas')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <Preferencia>
                <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />

                <CheckBox v-model:checked="elementosVisiveis.caixa" :label="__('Caixa')" />

                <CheckBox v-model:checked="elementosVisiveis.ano" :label="__('Ano')" />

                <CheckBox v-model:checked="elementosVisiveis.guardaPermanente" :label="__('GP')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.localidadeCriadora"
                    :label="__('Localidade criadora')"
                />

                <CheckBox
                    v-model:checked="elementosVisiveis.tipoProcesso"
                    :label="__('Tipo de processo')"
                />

                <CheckBox
                    v-model:checked="elementosVisiveis.complemento"
                    :label="__('Complemento')"
                />

                <CheckBox
                    v-model:checked="elementosVisiveis.processos"
                    :label="__('Qtd processos')"
                />

                <CheckBox
                    v-model:checked="elementosVisiveis.localidade"
                    :label="__('Localidade')"
                />

                <CheckBox v-model:checked="elementosVisiveis.predio" :label="__('Prédio')" />

                <CheckBox v-model:checked="elementosVisiveis.andarNumero" :label="__('Andar')" />

                <CheckBox v-model:checked="elementosVisiveis.andarApelido" :label="__('Apelido')" />

                <CheckBox v-model:checked="elementosVisiveis.sala" :label="__('Sala')" />

                <CheckBox v-model:checked="elementosVisiveis.estante" :label="__('Estante')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.prateleira"
                    :label="__('Prateleira')"
                />
            </Preferencia>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.caixa"
                        :ordenacao="ordenacoes.numero"
                        :texto="__('Caixa')"
                        @ordenar="(direcao) => mudarOrdenacao('numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.ano"
                        :ordenacao="ordenacoes.ano"
                        :texto="__('Ano')"
                        @ordenar="(direcao) => mudarOrdenacao('ano', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.guardaPermanente"
                        :ordenacao="ordenacoes.guarda_permanente"
                        :texto="__('GP')"
                        @ordenar="(direcao) => mudarOrdenacao('guarda_permanente', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.localidadeCriadora"
                        :ordenacao="ordenacoes.localidade_criadora_nome"
                        :texto="__('Localidade criadora')"
                        @ordenar="(direcao) => mudarOrdenacao('localidade_criadora_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.tipoProcesso"
                        :ordenacao="ordenacoes.tipo_processo_nome"
                        :texto="__('Tipo de processo')"
                        @ordenar="(direcao) => mudarOrdenacao('tipo_processo_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.complemento"
                        :ordenacao="ordenacoes.complemento"
                        :texto="__('Complemento')"
                        @ordenar="(direcao) => mudarOrdenacao('complemento', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.processos"
                        :ordenacao="ordenacoes.processos_count"
                        :texto="__('Qtd processos')"
                        @ordenar="(direcao) => mudarOrdenacao('processos_count', direcao)"
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

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.prateleira"
                        :ordenacao="ordenacoes.prateleira_pai_numero"
                        :texto="__('Prateleira')"
                        @ordenar="(direcao) => mudarOrdenacao('prateleira_pai_numero', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="caixas.data.length">
                        <Row v-for="caixa in caixas.data" :key="caixa.id">
                            <Cell v-show="elementosVisiveis.acao" class="w-10" fixo>
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="caixa.links.view"
                                        :href="caixa.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="caixa.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                caixa.links.delete,
                                                __('Exclusão da caixa :attribute', {
                                                    attribute: numeroCaixa(caixa),
                                                })
                                            )
                                        "
                                        especie="perigo"
                                        icone="trash"
                                    />
                                </div>
                            </Cell>

                            <Cell v-show="elementosVisiveis.caixa">{{ caixa.numero }}</Cell>

                            <Cell v-show="elementosVisiveis.ano">{{ caixa.ano }}</Cell>

                            <Cell v-show="elementosVisiveis.guardaPermanente">{{ gp(caixa) }}</Cell>

                            <Cell v-show="elementosVisiveis.localidadeCriadora">{{
                                caixa.localidade_criadora.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.tipoProcesso">{{
                                caixa.tipo_processo.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.complemento">{{
                                caixa.complemento
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.processos">{{
                                caixa.processos_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.localidade">{{
                                caixa.prateleira.estante.sala.andar.predio.localidade.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.predio">{{
                                caixa.prateleira.estante.sala.andar.predio.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.andarNumero">{{
                                caixa.prateleira.estante.sala.andar.numero
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.andarApelido">{{
                                caixa.prateleira.estante.sala.andar.apelido
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.sala">{{
                                caixa.prateleira.estante.sala.numero
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.estante">{{
                                caixa.prateleira.estante.numero
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.prateleira">{{
                                caixa.prateleira.numero
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

            <Paginacao v-if="caixas.meta.last_page > 1" :meta="caixas.meta" />
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
