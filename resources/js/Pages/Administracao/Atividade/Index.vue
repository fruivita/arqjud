<!--
    View para listagem (com filtros) das Atividades do usuário.

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
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import Pesquisa from '@/Shared/Forms/Pesquisa.vue';
import Clipboard from '@/Shared/Misc/Clipboard.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import HeadingOrdenavel from '@/Shared/Tables/HeadingOrdenavel.vue';
import Paginacao from '@/Shared/Tables/Paginacao.vue';
import Preferencia from '@/Shared/Tables/Preferencia.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { perPageKey, updatePerPageKey } from '@/keys';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';
import { useLocalStorage } from '@vueuse/core';
import { merge, pickBy } from 'lodash';
import { computed, provide, readonly, ref, watch } from 'vue';

const props = defineProps({
    atividades: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.atividades.meta.termo ?? '');

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.atividades.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    logName: true,
    event: true,
    uuid: true,
    description: true,
    subjectType: true,
    subjectId: true,
    causerType: true,
    causerId: true,
    matricula: true,
    properties: true,
    createdAt: true,
    updatedAt: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.atividades.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.atividades.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['atividades'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="__('Atividades do usuário')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <div class="flex flex-col space-y-3 md:flex-row md:items-start md:justify-end">
                <Preferencia>
                    <CheckBox
                        v-model:checked="elementosVisiveis.logName"
                        :label="__('Atividade')"
                    />

                    <CheckBox v-model:checked="elementosVisiveis.event" :label="__('Evento')" />

                    <CheckBox v-model:checked="elementosVisiveis.uuid" :label="__('UUID')" />

                    <CheckBox
                        v-model:checked="elementosVisiveis.description"
                        :label="__('Descrição')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.subjectType"
                        :label="__('Entidade')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.subjectId"
                        :label="__('Id da entidade')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.causerType"
                        :label="__('Causador')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.causerId"
                        :label="__('Id do causador')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.matricula"
                        :label="__('Matrícula')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.properties"
                        :label="__('Propriedades')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.createdAt"
                        :label="__('Criada em')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.updatedAt"
                        :label="__('Atualizada em')"
                    />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <HeadingOrdenavel
                        v-show="elementosVisiveis.logName"
                        :ordenacao="ordenacoes.log_name"
                        :texto="__('Atividade')"
                        @ordenar="(direcao) => mudarOrdenacao('log_name', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.event"
                        :ordenacao="ordenacoes.event"
                        :texto="__('Evento')"
                        @ordenar="(direcao) => mudarOrdenacao('event', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.uuid"
                        :ordenacao="ordenacoes.uuid"
                        :texto="__('UUID')"
                        @ordenar="(direcao) => mudarOrdenacao('uuid', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.description" :texto="__('Descrição')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.subjectType"
                        :ordenacao="ordenacoes.subject_type"
                        :texto="__('Entidade')"
                        @ordenar="(direcao) => mudarOrdenacao('subject_type', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.subjectId"
                        :ordenacao="ordenacoes.subject_id"
                        :texto="__('Id da entidade')"
                        @ordenar="(direcao) => mudarOrdenacao('subject_id', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.causerType"
                        :ordenacao="ordenacoes.causer_type"
                        :texto="__('Causador')"
                        @ordenar="(direcao) => mudarOrdenacao('causer_type', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.causerId"
                        :ordenacao="ordenacoes.causer_id"
                        :texto="__('Id do causador')"
                        @ordenar="(direcao) => mudarOrdenacao('causer_id', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.matricula"
                        :ordenacao="ordenacoes.matricula"
                        :texto="__('Matrícula')"
                        @ordenar="(direcao) => mudarOrdenacao('matricula', direcao)"
                    />

                    <Heading
                        v-show="elementosVisiveis.properties"
                        :texto="__('Propriedades')"
                        fixo
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.createdAt"
                        :ordenacao="ordenacoes.created_at"
                        :texto="__('Criada em')"
                        @ordenar="(direcao) => mudarOrdenacao('created_at', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.updatedAt"
                        :ordenacao="ordenacoes.updated_at"
                        :texto="__('Atualizada em')"
                        @ordenar="(direcao) => mudarOrdenacao('updated_at', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="atividades.data.length">
                        <Row v-for="atividade in atividades.data" :key="atividade.id">
                            <Cell v-show="elementosVisiveis.logName">{{ atividade.log_name }}</Cell>

                            <Cell v-show="elementosVisiveis.event">{{ atividade.event }}</Cell>

                            <Cell v-show="elementosVisiveis.uuid">
                                <span>{{ atividade.uuid }}</span>

                                <Clipboard
                                    v-if="atividade.uuid"
                                    :copiavel="atividade.uuid"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.description">{{
                                atividade.description
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.subjectType">{{
                                atividade.subject_type
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.subjectId">{{
                                atividade.subject_id
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.causerType">{{
                                atividade.causer_type
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.causerId">{{
                                atividade.causer_id
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.matricula">{{
                                atividade.matricula
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.properties">
                                <pre>{{ atividade.properties }}</pre>
                            </Cell>

                            <Cell v-show="elementosVisiveis.createdAt">{{
                                atividade.created_at
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.updatedAt">{{
                                atividade.updated_at
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

            <Paginacao v-if="atividades.meta.last_page > 1" :meta="atividades.meta" />
        </Container>
    </Pagina>
</template>
