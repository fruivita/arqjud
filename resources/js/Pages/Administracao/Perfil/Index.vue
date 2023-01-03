<!--
    View para listagem (com filtros) dos Perfis.

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
import InertiaButtonLink from '@/Shared/Inertia/InertiaButtonLink.vue';
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
    perfis: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.perfis.meta.termo ?? '');

const { confirmarExclusao, excluir, titulo } = useExclusao();

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.perfis.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    perfil: true,
    slug: true,
    poder: true,
    usuarios: true,
    delegados: true,
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
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
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
    <Pagina :titulo="__('Perfis')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <div
                :class="{
                    'md:justify-between': perfis.links.create,
                    'md:justify-end': !perfis.links.create,
                }"
                class="flex flex-col space-y-3 md:flex-row md:items-start"
            >
                <InertiaButtonLink
                    v-if="perfis.links.create"
                    :href="perfis.links.create"
                    :texto="__('Novo perfil')"
                    icone="plus-circle"
                />

                <Preferencia>
                    <CheckBox v-model:checked="elementosVisiveis.perfil" :label="__('Perfil')" />

                    <CheckBox v-model:checked="elementosVisiveis.slug" :label="__('Slug')" />

                    <CheckBox v-model:checked="elementosVisiveis.poder" :label="__('Poder')" />

                    <CheckBox
                        v-model:checked="elementosVisiveis.usuarios"
                        :label="__('Qtd usuários')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.delegados"
                        :label="__('Qtd delegados')"
                    />

                    <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <HeadingOrdenavel
                        v-show="elementosVisiveis.perfil"
                        :ordenacao="ordenacoes.nome"
                        :texto="__('Perfis')"
                        @ordenar="(direcao) => mudarOrdenacao('nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.slug"
                        :ordenacao="ordenacoes.slug"
                        :texto="__('Slug')"
                        @ordenar="(direcao) => mudarOrdenacao('slug', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.poder"
                        :ordenacao="ordenacoes.poder"
                        :texto="__('Poder')"
                        @ordenar="(direcao) => mudarOrdenacao('poder', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.usuarios"
                        :ordenacao="ordenacoes.usuarios_count"
                        :texto="__('Qtd usuários')"
                        @ordenar="(direcao) => mudarOrdenacao('usuarios_count', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.delegados"
                        :ordenacao="ordenacoes.delegados_count"
                        :texto="__('Qtd delegados')"
                        @ordenar="(direcao) => mudarOrdenacao('delegados_count', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="perfis.data.length">
                        <Row v-for="perfil in perfis.data" :key="perfil.id">
                            <Cell v-show="elementosVisiveis.perfil">{{ perfil.nome }}</Cell>

                            <Cell v-show="elementosVisiveis.slug">{{ perfil.slug }}</Cell>

                            <Cell v-show="elementosVisiveis.poder">{{ perfil.poder }}</Cell>

                            <Cell v-show="elementosVisiveis.usuarios">
                                {{ perfil.usuarios_count }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.delegados">
                                {{ perfil.delegados_count }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="perfil.links.view"
                                        :href="perfil.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="perfil.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                perfil.links.delete,
                                                __('Exclusão do perfil :attribute', {
                                                    attribute: perfil.nome,
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

            <Paginacao v-if="perfis.meta.last_page > 1" :meta="perfis.meta" />
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
