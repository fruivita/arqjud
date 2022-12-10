<!--
    View para visualização e edição do prédio.

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
    predio: { type: Object },
    andares: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    nome: props.predio.data.nome,
    descricao: props.predio.data.descricao ?? '',
});

const atualizar = () => {
    form.patch(props.predio.data.links.update, {
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
        ? __(':attribute: Modo Edição', { attribute: 'Prédio' })
        : __(':attribute: Modo Visualização', { attribute: 'Prédio' })
);

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.andares.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    andar: true,
    apelido: true,
    salas: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.andares.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.andares.meta.path,
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['andares'],
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
                            :href="predio.data.localidade.links.view"
                            :valor="predio.data.localidade.nome"
                            icone="pin-map"
                        />

                        <TextInput
                            v-model="form.nome"
                            :disabled="!modoEdicao"
                            :erro="form.errors.nome"
                            :label="__('Prédio')"
                            :maxlength="100"
                            :placeholder="__('Nome do prédio')"
                            autocomplete="off"
                            icone="buildings"
                            required
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :disabled="!modoEdicao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre o prédio')"
                        icone="blockquote-left"
                    />

                    <div
                        v-if="predio.data.links.update"
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
                    'md:justify-between': predio.data.links.create_andar,
                    'md:justify-end': !predio.data.links.create_andar,
                }"
                class="flex flex-col space-y-3 md:flex-row md:items-start"
            >
                <InertiaButtonLink
                    v-if="predio.data.links.create_andar"
                    :href="predio.data.links.create_andar"
                    :texto="__('Novo andar')"
                    icone="plus-circle"
                />

                <Preferencia>
                    <CheckBox v-model="elementosVisiveis.andar" :label="__('Andar')" />

                    <CheckBox v-model="elementosVisiveis.apelido" :label="__('Apelido')" />

                    <CheckBox v-model="elementosVisiveis.salas" :label="__('Qtd salas')" />

                    <CheckBox v-model="elementosVisiveis.acao" :label="__('Ações')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <HeadingOrdenavel
                        v-show="elementosVisiveis.andar"
                        :ordenacao="ordenacoes.numero"
                        :texto="__('Andar')"
                        @ordenar="(direcao) => mudarOrdenacao('numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.apelido"
                        :ordenacao="ordenacoes.apelido"
                        :texto="__('Apelido')"
                        @ordenar="(direcao) => mudarOrdenacao('apelido', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.salas"
                        :ordenacao="ordenacoes.salas_count"
                        :texto="__('Qtd salas')"
                        @ordenar="(direcao) => mudarOrdenacao('salas_count', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="andares.data.length">
                        <Row v-for="andar in andares.data" :key="andar.id">
                            <Cell v-show="elementosVisiveis.andar">{{ andar.numero }}</Cell>

                            <Cell v-show="elementosVisiveis.apelido">{{ andar.apelido }}</Cell>

                            <Cell v-show="elementosVisiveis.salas">{{ andar.salas_count }}</Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="andar.links.view"
                                        :href="andar.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="andar.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                andar.links.delete,
                                                new Andar(andar).numeroExibicao()
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

            <Paginacao v-if="andares.meta.last_page > 1" :meta="andares.meta" />
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
