<!--
    View para visualização e edição da Permissão.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { flash } from '@/Composables/UseFlash';
import { useOrdenacao } from '@/Composables/UseOrdenacao';
import { perPageKey, updatePerPageKey } from '@/keys';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
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
import { find, merge, pickBy } from 'lodash';
import { computed, provide, readonly, ref, watch } from 'vue';

const props = defineProps({
    permissao: { type: Object },
    perfis: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    nome: props.permissao.data.nome,
    descricao: props.permissao.data.descricao ?? '',
});

const atualizar = (perfil) => {
    form.transform((data) => ({
        ...data,
        perfil_id: perfil,
    })).patch(props.permissao.data.links.update, {
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
        ? __(':attribute: Modo Edição', { attribute: 'Permissão' })
        : __(':attribute: Modo Visualização', { attribute: 'Permissão' })
);

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.perfis.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    perfil: true,
    slug: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.perfis.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.perfis.meta.path,
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['perfis'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="tituloPagina">
        <Container>
            <form @submit.prevent="atualizar(null)">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                        <TextInput
                            v-model="form.nome"
                            :disabled="!modoEdicao"
                            :erro="form.errors.nome"
                            :label="__('Permissão')"
                            :maxlength="50"
                            :placeholder="__('Nome da permissão')"
                            autocomplete="off"
                            icone="vector-pen"
                        />

                        <ChaveValor
                            :chave="__('Slug')"
                            :erro="form.errors.slug"
                            :valor="permissao.data.slug"
                            icone="symmetry-vertical"
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :disabled="!modoEdicao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre a permissão')"
                        icone="blockquote-left"
                    />

                    <div
                        v-if="permissao.data.links.update"
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

                    <CheckBox v-model:checked="elementosVisiveis.perfil" :label="__('Perfil')" />

                    <CheckBox v-model:checked="elementosVisiveis.slug" :label="__('Slug')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.perfil"
                        :ordenacao="ordenacoes.nome"
                        :texto="__('Perfil')"
                        @ordenar="(direcao) => mudarOrdenacao('nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.slug"
                        :ordenacao="ordenacoes.slug"
                        :texto="__('Slug')"
                        @ordenar="(direcao) => mudarOrdenacao('slug', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="perfis.data.length">
                        <Row v-for="perfil in perfis.data" :key="perfil.id">
                            <Cell v-show="elementosVisiveis.acao" class="w-10" fixo>
                                <div class="flex space-x-3">
                                    <ButtonIcone
                                        v-if="perfil.links.update"
                                        :especie="
                                            find(perfil.permissoes, { id: permissao.data.id })
                                                ? 'padrao'
                                                : 'perigo'
                                        "
                                        :icone="
                                            find(perfil.permissoes, { id: permissao.data.id })
                                                ? 'hand-thumbs-up'
                                                : 'hand-thumbs-down'
                                        "
                                        @click="atualizar(perfil.id)"
                                    />

                                    <InertiaButtonIconeLink
                                        v-if="perfil.links.view"
                                        :href="perfil.links.view"
                                        icone="eye"
                                    />
                                </div>
                            </Cell>

                            <Cell v-show="elementosVisiveis.perfil">{{ perfil.nome }}</Cell>

                            <Cell v-show="elementosVisiveis.slug">{{ perfil.slug }}</Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell :colspan="colspan">{{ __('Nenhum registro encontrado') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>

            <Paginacao v-if="perfis.meta.last_page > 1" :meta="perfis.meta" />
        </Container>
    </Pagina>
</template>
