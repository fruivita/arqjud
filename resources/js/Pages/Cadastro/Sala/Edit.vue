<!--
    View para visualização e edição da sala.

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
import { numeroAndar } from '@/Helpers/Andar';
import { perPageKey, updatePerPageKey } from '@/keys';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
import InertiaButtonLink from '@/Shared/Inertia/InertiaButtonLink.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
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
    sala: { type: Object },
    estantes: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    numero: props.sala.data.numero,
    descricao: props.sala.data.descricao ?? '',
});

const atualizar = () => {
    form.patch(props.sala.data.links.update, {
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
        ? __(':attribute: Modo Edição', { attribute: 'Sala' })
        : __(':attribute: Modo Visualização', { attribute: 'Sala' })
);

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.estantes.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    estante: true,
    prateleiras: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.estantes.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.estantes.meta.path,
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['estantes'],
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
                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                        <ChaveValor
                            :chave="__('Localidade')"
                            :href="sala.data.andar.predio.localidade.links.view"
                            :valor="sala.data.andar.predio.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :href="sala.data.andar.predio.links.view"
                            :valor="sala.data.andar.predio.nome"
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :href="sala.data.andar.links.view"
                            :valor="numeroAndar(sala.data.andar)"
                            icone="layers"
                        />

                        <TextInput
                            v-model="form.numero"
                            :disabled="!modoEdicao"
                            :erro="form.errors.numero"
                            :label="__('Número da sala')"
                            :maxlength="50"
                            :placeholder="__('Ex.: 100, 100-F, ...')"
                            autocomplete="off"
                            icone="door-closed"
                            required
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :disabled="!modoEdicao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre a sala')"
                        icone="blockquote-left"
                    />

                    <div
                        v-if="sala.data.links.update"
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
            <div
                :class="{
                    'md:justify-between': sala.data.links.estante?.create,
                    'md:justify-end': !sala.data.links.estante?.create,
                }"
                class="flex flex-col space-y-3 md:flex-row md:items-start"
            >
                <InertiaButtonLink
                    v-if="sala.data.links.estante?.create"
                    :href="sala.data.links.estante.create"
                    :texto="__('Nova estante')"
                    icone="plus-circle"
                />

                <Preferencia>
                    <CheckBox v-model:checked="elementosVisiveis.estante" :label="__('Estante')" />

                    <CheckBox
                        v-model:checked="elementosVisiveis.prateleiras"
                        :label="__('Qtd prateleiras')"
                    />

                    <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <HeadingOrdenavel
                        v-show="elementosVisiveis.estante"
                        :ordenacao="ordenacoes.numero"
                        :texto="__('Estante')"
                        @ordenar="(direcao) => mudarOrdenacao('numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.prateleiras"
                        :ordenacao="ordenacoes.prateleiras_count"
                        :texto="__('Qtd prateleiras')"
                        @ordenar="(direcao) => mudarOrdenacao('prateleiras_count', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="estantes.data.length">
                        <Row v-for="estante in estantes.data" :key="estante.id">
                            <Cell v-show="elementosVisiveis.estante">{{ estante.numero }}</Cell>

                            <Cell v-show="elementosVisiveis.prateleiras">{{
                                estante.prateleiras_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="estante.links.view"
                                        :href="estante.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="estante.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                estante.links.delete,
                                                __('Exclusão da estante :attribute', {
                                                    attribute: estante.numero,
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

            <Paginacao v-if="estantes.meta.last_page > 1" :meta="estantes.meta" />
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
