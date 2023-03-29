<!--
    View para visualização e edição do Perfil.

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
    perfil: { type: Object },
    permissoes: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    nome: props.perfil.data.nome,
    descricao: props.perfil.data.descricao ?? '',
});

const atualizar = (permissao) => {
    form.transform((data) => ({
        ...data,
        permissao_id: permissao,
    })).patch(props.perfil.data.links.update, {
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
        ? __(':attribute: Modo Edição', { attribute: 'Perfil' })
        : __(':attribute: Modo Visualização', { attribute: 'Perfil' })
);

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.permissoes.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    permissao: true,
    slug: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.permissoes.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.permissoes.meta.path,
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['permissoes'],
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
                            :label="__('Perfil')"
                            :maxlength="50"
                            :placeholder="__('Nome do perfil')"
                            autocomplete="off"
                            icone="award"
                        />

                        <ChaveValor
                            :chave="__('Slug')"
                            :erro="form.errors.slug"
                            :valor="perfil.data.slug"
                            icone="symmetry-vertical"
                        />

                        <div>
                            <ChaveValor
                                :chave="__('Poder')"
                                :valor="perfil.data.poder"
                                icone="p-circle"
                            />

                            <p class="text-sm">
                                {{ __('Usado para estabelecer a hierarquia entre os perfis.') }}
                            </p>
                        </div>
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :disabled="!modoEdicao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre o perfil')"
                        icone="blockquote-left"
                    />

                    <div
                        v-if="perfil.data.links.update"
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

                    <CheckBox
                        v-model:checked="elementosVisiveis.permissao"
                        :label="__('Permissão')"
                    />

                    <CheckBox v-model:checked="elementosVisiveis.slug" :label="__('Slug')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.permissao"
                        :ordenacao="ordenacoes.nome"
                        :texto="__('Permissao')"
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
                    <template v-if="permissoes.data.length">
                        <Row v-for="permissao in permissoes.data" :key="permissao.id">
                            <Cell v-show="elementosVisiveis.acao" class="w-10" fixo>
                                <div class="flex space-x-3">
                                    <ButtonIcone
                                        v-if="permissao.links.update"
                                        :especie="
                                            find(permissao.perfis, { id: perfil.data.id })
                                                ? 'padrao'
                                                : 'perigo'
                                        "
                                        :icone="
                                            find(permissao.perfis, { id: perfil.data.id })
                                                ? 'hand-thumbs-up'
                                                : 'hand-thumbs-down'
                                        "
                                        @click="atualizar(permissao.id)"
                                    />

                                    <InertiaButtonIconeLink
                                        v-if="permissao.links.view"
                                        :href="permissao.links.view"
                                        icone="eye"
                                    />
                                </div>
                            </Cell>

                            <Cell v-show="elementosVisiveis.permissao">{{ permissao.nome }}</Cell>

                            <Cell v-show="elementosVisiveis.slug">{{ permissao.slug }}</Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell :colspan="colspan">{{ __('Nenhum registro encontrado!') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>

            <Paginacao v-if="permissoes.meta.last_page > 1" :meta="permissoes.meta" />
        </Container>
    </Pagina>
</template>
