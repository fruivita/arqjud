<!--
    View para visualização e edição do tipo de processo.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { useExclusao } from '@/Composables/UseExclusao';
import { flash } from '@/Composables/UseFlash';
import { useOrdenacao } from '@/Composables/UseOrdenacao';
import { gp, numeroCaixa } from '@/Helpers/Caixa';
import { perPageKey, updatePerPageKey } from '@/keys';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
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
import { useForm, usePage } from '@inertiajs/inertia-vue3';
import { useLocalStorage } from '@vueuse/core';
import { merge, pickBy } from 'lodash';
import { computed, provide, readonly, ref, watch } from 'vue';

const props = defineProps({
    tipo_processo: { type: Object },
    caixas: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    nome: props.tipo_processo.data.nome,
    descricao: props.tipo_processo.data.descricao ?? '',
});

const atualizar = () => {
    form.patch(props.tipo_processo.data.links.update, {
        preserveScroll: true,
        onSuccess: () => {
            flash();
            modoEdicao.value = false;
        },
    });
};

const cancelarEdicao = () => {
    form.reset();
    form.clearErrors();
    modoEdicao.value = false;
};

const tituloPagina = computed(() =>
    modoEdicao.value === true
        ? __(':attribute: Modo Edição', { attribute: 'Tipo de processo' })
        : __(':attribute: Modo Visualização', { attribute: 'Tipo de processo' })
);

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.caixas.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    caixa: true,
    ano: true,
    guardaPermanente: true,
    complemento: true,
    localidadeCriadora: true,
    processos: true,
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
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
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

const { confirmarExclusao, excluir, titulo } = useExclusao();
</script>

<template>
    <Pagina :titulo="tituloPagina">
        <Container>
            <form @submit.prevent="atualizar">
                <div class="space-y-6">
                    <TextInput
                        v-model="form.nome"
                        :disabled="!modoEdicao"
                        :erro="form.errors.nome"
                        :label="__('Tipo de processo')"
                        :maxlength="100"
                        :placeholder="__('Nome do tipo de processo')"
                        autocomplete="off"
                        icone="pin-map"
                        required
                    />

                    <TextAreaInput
                        v-model="form.descricao"
                        :disabled="!modoEdicao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre o tipo de processo')"
                        icone="blockquote-left"
                    />

                    <div
                        v-if="tipo_processo.data.links.update"
                        class="flex flex-col justify-end space-y-3 space-x-0 md:flex-row md:space-y-0 md:space-x-3"
                    >
                        <ButtonText
                            v-if="!modoEdicao"
                            :texto="__('Editar')"
                            @click="modoEdicao = true"
                            dusk="editar"
                            icone="pencil-square"
                            type="button"
                        />

                        <ButtonText
                            v-if="modoEdicao"
                            :texto="__('Salvar')"
                            dusk="atualizar"
                            especie="acao"
                            icone="save"
                            type="submit"
                        />

                        <ButtonText
                            v-if="modoEdicao"
                            :texto="__('Cancelar')"
                            @click="cancelarEdicao"
                            dusk="cancelar"
                            especie="inacao"
                            icone="x-circle"
                            type="button"
                        />
                    </div>
                </div>
            </form>
        </Container>

        <Container class="space-y-3">
            <div class="flex flex-col space-y-3 md:flex-row md:items-start md:justify-end">
                <Preferencia>
                    <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />

                    <CheckBox v-model:checked="elementosVisiveis.caixa" :label="__('Caixa')" />

                    <CheckBox v-model:checked="elementosVisiveis.ano" :label="__('Ano')" />

                    <CheckBox
                        v-model:checked="elementosVisiveis.guardaPermanente"
                        :label="__('GP')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.localidadeCriadora"
                        :label="__('Localidade criadora')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.complemento"
                        :label="__('Complemento')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.processos"
                        :label="__('Qtd processos')"
                    />
                </Preferencia>
            </div>

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

                            <Cell v-show="elementosVisiveis.complemento">{{
                                caixa.complemento
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.processos">{{
                                caixa.processos_count
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
