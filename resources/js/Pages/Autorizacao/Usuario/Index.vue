<!--
    View para listagem (com filtros) dos usuários.

    Notar que:
    - As preferências de exibição são armazenadas no cache do navegador.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
    @see https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { countElementosVisiveis } from '@/Composables/UseCountElementosVisiveis';
import { useOrdenacao } from '@/Composables/UseOrdenacao';
import { perPageKey, updatePerPageKey } from '@/keys';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import Pesquisa from '@/Shared/Forms/Pesquisa.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
import Tooltip from '@/Shared/Misc/Tooltip.vue';
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
    usuarios: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.usuarios.meta.termo ?? '');

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.usuarios.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    status: true,
    nome: true,
    matricula: true,
    email: true,
    lotacao: true,
    cargo: true,
    funcao: true,
    perfil: true,
    ultimoLogin: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.usuarios.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.usuarios.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['usuarios'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Usuários')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" maxlength="25" />
        </form>

        <Container class="space-y-3">
            <Preferencia>
                <CheckBox v-model:checked="elementosVisiveis.status" :label="__('Cadastro')" />

                <CheckBox v-model:checked="elementosVisiveis.nome" :label="__('Nome')" />

                <CheckBox v-model:checked="elementosVisiveis.matricula" :label="__('Matrícula')" />

                <CheckBox v-model:checked="elementosVisiveis.email" :label="__('Email')" />

                <CheckBox v-model:checked="elementosVisiveis.lotacao" :label="__('Lotação')" />

                <CheckBox v-model:checked="elementosVisiveis.cargo" :label="__('Cargo')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.funcao"
                    :label="__('Função de confiança')"
                />

                <CheckBox v-model:checked="elementosVisiveis.perfil" :label="__('Perfil')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.ultimoLogin"
                    :label="__('Último login')"
                />

                <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />
            </Preferencia>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.status" :texto="__('Cadastro')" />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.nome"
                        :ordenacao="ordenacoes.nome"
                        :texto="__('Nome')"
                        @ordenar="(direcao) => mudarOrdenacao('nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.matricula"
                        :ordenacao="ordenacoes.matricula"
                        :texto="__('Matrícula')"
                        @ordenar="(direcao) => mudarOrdenacao('matricula', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.email"
                        :ordenacao="ordenacoes.email"
                        :texto="__('Email')"
                        @ordenar="(direcao) => mudarOrdenacao('email', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.lotacao"
                        :ordenacao="ordenacoes.lotacao_sigla"
                        :texto="__('Lotação')"
                        @ordenar="(direcao) => mudarOrdenacao('lotacao_sigla', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.cargo"
                        :ordenacao="ordenacoes.cargo_nome"
                        :texto="__('Cargo')"
                        @ordenar="(direcao) => mudarOrdenacao('cargo_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.funcao"
                        :ordenacao="ordenacoes.funcao_nome"
                        :texto="__('Função de confiança')"
                        @ordenar="(direcao) => mudarOrdenacao('funcao_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.perfil"
                        :ordenacao="ordenacoes.perfil_nome"
                        :texto="__('Perfil')"
                        @ordenar="(direcao) => mudarOrdenacao('perfil_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.ultimoLogin"
                        :ordenacao="ordenacoes.ultimo_login"
                        :texto="__('Último login')"
                        @ordenar="(direcao) => mudarOrdenacao('ultimo_login', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="usuarios.data.length">
                        <Row v-for="usuario in usuarios.data" :key="usuario.id">
                            <Cell v-show="elementosVisiveis.status">
                                <span
                                    :class="{
                                        'bg-yellow-500 text-yellow-50':
                                            usuario.status == __('incompleto'),
                                        'bg-green-500 text-green-50':
                                            usuario.status == __('completo'),
                                    }"
                                    class="rounded-full px-2 py-1 font-mono text-sm font-bold"
                                >
                                    {{ usuario.status }}
                                </span>
                            </Cell>

                            <Cell v-show="elementosVisiveis.nome">{{ usuario.nome }}</Cell>

                            <Cell v-show="elementosVisiveis.matricula">{{
                                usuario.matricula
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.email">{{ usuario.email }}</Cell>

                            <Cell v-show="elementosVisiveis.lotacao">
                                <span>{{ usuario.lotacao?.sigla }}</span>

                                <Tooltip
                                    v-if="usuario.lotacao?.nome"
                                    :texto="usuario.lotacao?.nome"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.cargo">{{ usuario.cargo?.nome }}</Cell>

                            <Cell v-show="elementosVisiveis.funcao">{{
                                usuario.funcao?.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.perfil">{{
                                usuario.perfil?.nome
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.ultimoLogin">
                                <span>{{ usuario.ultimo_login }}</span>

                                <Tooltip v-if="usuario.ip" :texto="usuario.ip" class="ml-1" />
                            </Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="usuario.links.view"
                                        :href="usuario.links.view"
                                        icone="eye"
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

            <Paginacao v-if="usuarios.meta.last_page > 1" :meta="usuarios.meta" />
        </Container>
    </Pagina>
</template>
