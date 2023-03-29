<!--
    View para visualização e edição da caixa.

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
import { gp } from '@/Helpers/Processo';
import { perPageKey, updatePerPageKey } from '@/keys';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Alerta from '@/Shared/Containers/Alerta.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import NumeroInput from '@/Shared/Forms/NumeroInput.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
import InertiaButtonLink from '@/Shared/Inertia/InertiaButtonLink.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import Clipboard from '@/Shared/Misc/Clipboard.vue';
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
    caixa: { type: Object },
    processos: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    localidade_criadora_id: props.caixa.data.localidade_criadora_id,
    numero: props.caixa.data.numero,
    ano: props.caixa.data.ano,
    guarda_permanente: props.caixa.data.guarda_permanente,
    complemento: props.caixa.data.complemento ?? '',
    descricao: props.caixa.data.descricao ?? '',
});

const atualizar = () => {
    form.patch(props.caixa.data.links.update, {
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
        ? __(':attribute: Modo Edição', { attribute: 'Caixa' })
        : __(':attribute: Modo Visualização', { attribute: 'Caixa' })
);

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.processos.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    processo: true,
    numeroAntigo: true,
    arquivadoEm: true,
    guardaPermanente: true,
    volCaixaInicial: true,
    volCaixaFinal: true,
    qtdVolumes: true,
    processosFilho: true,
    solicitacoes: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.processos.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.processos.meta.path,
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['processos'],
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
                            :href="
                                caixa.data.prateleira.estante.sala.andar.predio.localidade.links
                                    .view
                            "
                            :valor="caixa.data.prateleira.estante.sala.andar.predio.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :href="caixa.data.prateleira.estante.sala.andar.predio.links.view"
                            :valor="caixa.data.prateleira.estante.sala.andar.predio.nome"
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :href="caixa.data.prateleira.estante.sala.andar.links.view"
                            :valor="numeroAndar(caixa.data.prateleira.estante.sala.andar)"
                            icone="layers"
                        />

                        <ChaveValor
                            :chave="__('Sala')"
                            :href="caixa.data.prateleira.estante.sala.links.view"
                            :valor="caixa.data.prateleira.estante.sala.numero"
                            icone="door-closed"
                        />

                        <ChaveValor
                            :chave="__('Estante')"
                            :href="caixa.data.prateleira.estante.links.view"
                            :valor="caixa.data.prateleira.estante.numero"
                            icone="bookshelf"
                        />

                        <ChaveValor
                            :chave="__('Prateleira')"
                            :href="caixa.data.prateleira.links.view"
                            :valor="caixa.data.prateleira.numero"
                            icone="list-nested"
                        />

                        <ChaveValor
                            :chave="__('Localidade criadora')"
                            :href="caixa.data.localidade_criadora.links.view"
                            :valor="caixa.data.localidade_criadora.nome"
                            icone="list-nested"
                            :erro="form.errors.localidade_criadora_id"
                        />

                        <NumeroInput
                            v-model="form.numero"
                            :disabled="!modoEdicao"
                            :erro="form.errors.numero"
                            :label="__('Número da caixa')"
                            :max="9999999"
                            :min="1"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="box2"
                            required
                        />

                        <NumeroInput
                            v-model="form.ano"
                            :disabled="!modoEdicao"
                            :erro="form.errors.ano"
                            :label="__('Ano da caixa')"
                            :max="new Date().getFullYear()"
                            :min="1900"
                            :placeholder="__('aaaa')"
                            autocomplete="off"
                            icone="calendar-range"
                            required
                        />

                        <TextInput
                            v-model="form.complemento"
                            :disabled="!modoEdicao"
                            :erro="form.errors.complemento"
                            :label="__('Complemento do número')"
                            :maxlength="50"
                            :placeholder="__('Ex.: Cri, Civ, ...')"
                            autocomplete="off"
                            icone="quote"
                        />

                        <CheckBox
                            v-model:checked="form.guarda_permanente"
                            :disabled="!modoEdicao"
                            :label="__('Guarda Permanente')"
                        />
                    </div>

                    <Alerta v-show="modoEdicao">
                        <p>
                            {{
                                __(
                                    'Todos os processos da caixa terão o seu status de guarda permanente alterado para o valor aqui definido.'
                                )
                            }}
                        </p>
                    </Alerta>

                    <TextAreaInput
                        v-model="form.descricao"
                        :disabled="!modoEdicao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre a caixa')"
                        icone="blockquote-left"
                    />

                    <div
                        v-if="caixa.data.links.update"
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
                    'md:justify-between': caixa.data.links.processo?.create,
                    'md:justify-end': !caixa.data.links.processo?.create,
                }"
                class="flex flex-col space-y-3 md:flex-row md:items-start"
            >
                <InertiaButtonLink
                    v-if="caixa.data.links.processo?.create"
                    :href="caixa.data.links.processo.create"
                    :texto="__('Novo processo')"
                    icone="plus-circle"
                />

                <Preferencia>
                    <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />

                    <CheckBox
                        v-model:checked="elementosVisiveis.processo"
                        :label="__('Processo')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.numeroAntigo"
                        :label="__('Número antigo')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.arquivadoEm"
                        :label="__('Arquivado em')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.guardaPermanente"
                        :label="__('GP')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.qtdVolumes"
                        :label="__('Volumes')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.volCaixaInicial"
                        :label="__('Vol caixa inicial')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.volCaixaFinal"
                        :label="__('Vol caixa final')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.processosFilho"
                        :label="__('Qtd proc filho')"
                    />

                    <CheckBox
                        v-model:checked="elementosVisiveis.solicitacoes"
                        :label="__('Qtd solicitações')"
                    />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" fixo />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.processo"
                        :ordenacao="ordenacoes.numero"
                        :texto="__('Processo')"
                        @ordenar="(direcao) => mudarOrdenacao('numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.numeroAntigo"
                        :ordenacao="ordenacoes.numero_antigo"
                        :texto="__('Número antigo')"
                        @ordenar="(direcao) => mudarOrdenacao('numero_antigo', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.arquivadoEm"
                        :ordenacao="ordenacoes.arquivado_em"
                        :texto="__('Arquivado em')"
                        @ordenar="(direcao) => mudarOrdenacao('arquivado_em', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.guardaPermanente"
                        :ordenacao="ordenacoes.guarda_permanente"
                        :texto="__('GP')"
                        @ordenar="(direcao) => mudarOrdenacao('guarda_permanente', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.qtdVolumes"
                        :ordenacao="ordenacoes.qtd_volumes"
                        :texto="__('Volumes')"
                        @ordenar="(direcao) => mudarOrdenacao('qtd_volumes', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.volCaixaInicial"
                        :ordenacao="ordenacoes.vol_caixa_inicial"
                        :texto="__('Vol caixa inicial')"
                        @ordenar="(direcao) => mudarOrdenacao('vol_caixa_inicial', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.volCaixaFinal"
                        :ordenacao="ordenacoes.vol_caixa_final"
                        :texto="__('Vol caixa final')"
                        @ordenar="(direcao) => mudarOrdenacao('vol_caixa_final', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.processosFilho"
                        :ordenacao="ordenacoes.processos_filho_count"
                        :texto="__('Qtd proc filho')"
                        @ordenar="(direcao) => mudarOrdenacao('processos_filho_count', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.solicitacoes"
                        :ordenacao="ordenacoes.solicitacoes_count"
                        :texto="__('Qtd solicitações')"
                        @ordenar="(direcao) => mudarOrdenacao('solicitacoes_count', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="processos.data.length">
                        <Row v-for="processo in processos.data" :key="processo.id">
                            <Cell v-show="elementosVisiveis.acao" class="w-10" fixo>
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="processo.links.view"
                                        :href="processo.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="processo.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                processo.links.delete,
                                                __('Exclusão do processo :attribute', {
                                                    attribute: processo.numero,
                                                })
                                            )
                                        "
                                        especie="perigo"
                                        icone="trash"
                                    />
                                </div>
                            </Cell>

                            <Cell v-show="elementosVisiveis.processo">
                                <span>{{ processo.numero }}</span>

                                <Clipboard :copiavel="processo.numero" class="ml-1" />
                            </Cell>

                            <Cell v-show="elementosVisiveis.numeroAntigo">
                                <span>{{ processo.numero_antigo }}</span>

                                <Clipboard
                                    v-if="processo.numero_antigo"
                                    :copiavel="processo.numero_antigo"
                                    class="ml-1"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.arquivadoEm">{{
                                processo.arquivado_em
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.guardaPermanente">{{
                                gp(processo)
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.qtdVolumes">{{
                                processo.qtd_volumes
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.volCaixaInicial">{{
                                processo.vol_caixa_inicial
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.volCaixaFinal">{{
                                processo.vol_caixa_final
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.processosFilho">{{
                                processo.processos_filho_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.solicitacoes">{{
                                processo.solicitacoes_count
                            }}</Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell :colspan="colspan">{{ __('Nenhum registro encontrado!') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>

            <Paginacao v-if="processos.meta.last_page > 1" :meta="processos.meta" />
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
