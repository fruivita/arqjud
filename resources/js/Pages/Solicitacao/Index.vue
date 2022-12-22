<!--
    View para listagem das solicitações de processo da lotação do próprio
    usuário autenticado.

    Notar que:
    - As preferências de exibição são armazenadas no cache do navegador.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://www.thisdot.co/blog/provide-inject-api-with-vue-3
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
import InertiaButtonLink from '@/Shared/Inertia/InertiaButtonLink.vue';
import Card from '@/Shared/Misc/Card.vue';
import Clipboard from '@/Shared/Misc/Clipboard.vue';
import Tooltip from '@/Shared/Misc/Tooltip.vue';
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
    solicitacoes: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.solicitacoes.meta.termo ?? '');

const { confirmarExclusao, excluir, titulo } = useExclusao();

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.solicitacoes.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    status: true,
    processo: true,
    lotacaoDestinataria: true,
    solicitante: true,
    solicitadaEm: true,
    remetente: true,
    recebedor: true,
    entregueEm: true,
    rearquivador: true,
    devolvidaEm: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.solicitacoes.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.solicitacoes.meta.path,
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['solicitacoes'],
        }
    );
};

watch(ordenacoes, filtrar, { deep: true });
watch(perPage, filtrar);
</script>

<template>
    <Pagina
        :titulo="
            __('Solicitações de :attribute', {
                attribute: solicitacoes.meta.lotacao_destinataria.sigla,
            })
        "
    >
        <div class="grid grid-cols-1 gap-x-3 gap-y-6 md:grid-cols-3">
            <Card
                :texto="solicitacoes.meta.count.solicitadas"
                :titulo="__('Solicitadas')"
                especie="perigo"
            />

            <Card
                :texto="solicitacoes.meta.count.entregues"
                :titulo="__('Entregues')"
                especie="alerta"
            />

            <Card
                :texto="solicitacoes.meta.count.devolvidas"
                :titulo="__('Devolvidas')"
                especie="info"
            />
        </div>

        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" maxlength="25" />
        </form>

        <Container class="space-y-3">
            <div
                :class="{
                    'md:justify-between': solicitacoes.links.create,
                    'md:justify-end': !solicitacoes.links.create,
                }"
                class="flex flex-col space-y-3 md:flex-row md:items-start"
            >
                <InertiaButtonLink
                    v-if="solicitacoes.links.create"
                    :href="solicitacoes.links.create"
                    :texto="__('Solicitar processo')"
                    icone="plus-circle"
                />

                <Preferencia>
                    <CheckBox v-model="elementosVisiveis.status" :label="__('Status')" />

                    <CheckBox v-model="elementosVisiveis.processo" :label="__('Processo')" />

                    <CheckBox
                        v-model="elementosVisiveis.lotacaoDestinataria"
                        :label="__('Lotação destinatária')"
                    />

                    <CheckBox v-model="elementosVisiveis.solicitante" :label="__('Solicitante')" />

                    <CheckBox
                        v-model="elementosVisiveis.solicitadaEm"
                        :label="__('Solicitada em')"
                    />

                    <CheckBox v-model="elementosVisiveis.remetente" :label="__('Remetente')" />

                    <CheckBox v-model="elementosVisiveis.recebedor" :label="__('Recebedor')" />

                    <CheckBox v-model="elementosVisiveis.entregueEm" :label="__('Entregue em')" />

                    <CheckBox
                        v-model="elementosVisiveis.rearquivador"
                        :label="__('Rearquivada por')"
                    />

                    <CheckBox v-model="elementosVisiveis.devolvidaEm" :label="__('Devolvida em')" />

                    <CheckBox v-model="elementosVisiveis.acao" :label="__('Ações')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.status" :texto="__('Status')" />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.processo"
                        :ordenacao="ordenacoes.processo_numero"
                        :texto="__('Processo')"
                        @ordenar="(direcao) => mudarOrdenacao('processo_numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.lotacaoDestinataria"
                        :ordenacao="ordenacoes.lotacao_destinataria_sigla"
                        :texto="__('Lotação destinatária')"
                        @ordenar="
                            (direcao) => mudarOrdenacao('lotacao_destinataria_sigla', direcao)
                        "
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.solicitante"
                        :ordenacao="ordenacoes.solicitante_username"
                        :texto="__('Solicitante')"
                        @ordenar="(direcao) => mudarOrdenacao('solicitante_username', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.solicitadaEm"
                        :ordenacao="ordenacoes.solicitada_em"
                        :texto="__('Solicitada em')"
                        @ordenar="(direcao) => mudarOrdenacao('solicitada_em', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.remetente"
                        :ordenacao="ordenacoes.remetente_username"
                        :texto="__('Remetente')"
                        @ordenar="(direcao) => mudarOrdenacao('remetente_username', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.recebedor"
                        :ordenacao="ordenacoes.recebedor_username"
                        :texto="__('Recebedor')"
                        @ordenar="(direcao) => mudarOrdenacao('recebedor_username', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.entregueEm"
                        :ordenacao="ordenacoes.entregue_em"
                        :texto="__('Entregue em')"
                        @ordenar="(direcao) => mudarOrdenacao('entregue_em', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.rearquivador"
                        :ordenacao="ordenacoes.rearquivador_username"
                        :texto="__('Rearquivada por')"
                        @ordenar="(direcao) => mudarOrdenacao('rearquivador_username', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.devolvidaEm"
                        :ordenacao="ordenacoes.devolvida_em"
                        :texto="__('Devolvida em')"
                        @ordenar="(direcao) => mudarOrdenacao('devolvida_em', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="solicitacoes.data.length">
                        <Row v-for="solicitacao in solicitacoes.data" :key="solicitacao.id">
                            <Cell v-show="elementosVisiveis.status">
                                <span
                                    :class="{
                                        'bg-red-500 text-red-50':
                                            solicitacao.status == __('solicitada'),
                                        'bg-yellow-500 text-yellow-50':
                                            solicitacao.status == __('entregue'),
                                        'bg-green-500 text-green-50':
                                            solicitacao.status == __('devolvida'),
                                    }"
                                    class="rounded-full px-2 py-1 font-mono text-sm font-bold"
                                >
                                    {{ solicitacao.status }}
                                </span>
                            </Cell>

                            <Cell v-show="elementosVisiveis.processo">
                                <span>{{ solicitacao.processo.numero }}</span>

                                <Clipboard :copiavel="solicitacao.processo.numero" class="ml-1" />

                                <Tooltip
                                    v-if="solicitacao.processo.numero_antigo"
                                    :texto="solicitacao.processo.numero_antigo"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.lotacaoDestinataria">
                                <span>{{ solicitacao.lotacao_destinataria.sigla }}</span>

                                <Tooltip
                                    v-if="solicitacao.lotacao_destinataria.nome"
                                    :texto="solicitacao.lotacao_destinataria.nome"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.solicitante">
                                <span>{{ solicitacao.solicitante.username }}</span>

                                <Tooltip
                                    v-if="solicitacao.solicitante.nome"
                                    :texto="solicitacao.solicitante.nome"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.solicitadaEm">
                                {{ solicitacao.solicitada_em }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.remetente">
                                <span>{{ solicitacao.remetente?.username }}</span>

                                <Tooltip
                                    v-if="solicitacao.remetente?.nome"
                                    :texto="solicitacao.remetente.nome"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.recebedor">
                                <span>{{ solicitacao.recebedor?.username }}</span>

                                <Tooltip
                                    v-if="solicitacao.recebedor?.nome"
                                    :texto="solicitacao.recebedor.nome"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.entregueEm">
                                {{ solicitacao.entregue_em }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.rearquivador">
                                <span>{{ solicitacao.rearquivador?.username }}</span>

                                <Tooltip
                                    v-if="solicitacao.rearquivador?.nome"
                                    :texto="solicitacao.rearquivador.nome"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.devolvidaEm">
                                {{ solicitacao.devolvida_em }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
                                <div class="flex space-x-3">
                                    <ButtonIcone
                                        v-if="solicitacao.links.externo_delete"
                                        @click="
                                            confirmarExclusao(
                                                solicitacao.links.externo_delete,
                                                __(
                                                    'Exclusão da solicitação do processo :attribute',
                                                    { attribute: solicitacao.processo.numero }
                                                )
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

            <Paginacao v-if="solicitacoes.meta.last_page > 1" :meta="solicitacoes.meta" />
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
