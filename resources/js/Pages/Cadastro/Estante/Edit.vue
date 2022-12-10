<!--
    View para visualização e edição da estante.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { useExclusao } from '@/Composables/useExclusao';
import { flash } from '@/Composables/useFlash';
import { useOrdenacao } from '@/Composables/UseOrdenacao';
import { perPageKey, updatePerPageKey } from '@/keys.js';
import Andar from '@/Models/Andar';
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
    estante: { type: Object },
    prateleiras: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    numero: props.estante.data.numero,
    descricao: props.estante.data.descricao ?? '',
});

const atualizar = () => {
    form.patch(props.estante.data.links.update, {
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
        ? __(':attribute: Modo Edição', { attribute: 'Estante' })
        : __(':attribute: Modo Visualização', { attribute: 'Estante' })
);

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.prateleiras.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    prateleira: true,
    caixas: true,
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
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
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
                            :valor="estante.data.sala.andar.predio.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :valor="estante.data.sala.andar.predio.nome"
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :valor="new Andar(estante.data.sala.andar).numeroExibicao()"
                            icone="layers"
                        />

                        <ChaveValor
                            :chave="__('Sala')"
                            :valor="estante.data.sala.numero"
                            icone="door-closed"
                        />

                        <TextInput
                            v-model="form.numero"
                            :disabled="!modoEdicao"
                            :erro="form.errors.numero"
                            :label="__('Número da estante')"
                            :maxlength="50"
                            :placeholder="__('Ex.: 100, 100-F, ...')"
                            autocomplete="off"
                            icone="bookshelf"
                            required
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :disabled="!modoEdicao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre a estante')"
                        icone="blockquote-left"
                    />

                    <div
                        v-if="estante.data.links.update"
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
                    'md:justify-between': estante.data.links.create_prateleira,
                    'md:justify-end': !estante.data.links.create_prateleira,
                }"
                class="flex flex-col space-y-3 md:flex-row md:items-start"
            >
                <InertiaButtonLink
                    v-if="estante.data.links.create_prateleira"
                    :href="estante.data.links.create_prateleira"
                    :texto="__('Nova prateleira')"
                    icone="plus-circle"
                />

                <Preferencia>
                    <CheckBox v-model="elementosVisiveis.prateleira" :label="__('Prateleira')" />

                    <CheckBox v-model="elementosVisiveis.caixas" :label="__('Qtd caixas')" />

                    <CheckBox v-model="elementosVisiveis.acao" :label="__('Ações')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
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

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="prateleiras.data.length">
                        <Row v-for="prateleira in prateleiras.data" :key="prateleira.id">
                            <Cell v-show="elementosVisiveis.prateleira">{{
                                prateleira.numero
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.caixas">{{
                                prateleira.caixas_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
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
                                                prateleira.numero
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
