<!--
    View para visualização e edição do processo.

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
import { mascaraCNJ, perPageKey, updatePerPageKey } from '@/keys.js';
import Andar from '@/Models/Andar';
import Caixa from '@/Models/Caixa';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import NumeroInput from '@/Shared/Forms/NumeroInput.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
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
    processo: { type: Object },
    processos_filho: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    numero: props.processo.data.numero,
    numero_antigo: props.processo.data.numero_antigo,
    arquivado_em: props.processo.data.arquivado_em,
    qtd_volumes: props.processo.data.qtd_volumes,
    processo_pai_numero: props.processo.data.processo_pai?.numero ?? '',
    descricao: props.processo.data.descricao ?? '',
});

const atualizar = () => {
    form.patch(props.processo.data.links.update, {
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
        ? __(':attribute: Modo Edição', { attribute: 'Processo' })
        : __(':attribute: Modo Visualização', { attribute: 'Processo' })
);

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.processos_filho.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    processo: true,
    numeroAntigo: true,
    arquivadoEm: true,
    guardaPermanente: true,
    qtdVolumes: true,
    processosFilho: true,
    solicitacoes: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.processos_filho.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.processos_filho.meta.path,
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['processos_filho'],
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
                            :href="processo.data.volume_caixa.caixa.prateleira.estante.sala.andar.predio.localidade.links.view"
                            :valor="
                                processo.data.volume_caixa.caixa.prateleira.estante.sala.andar
                                    .predio.localidade.nome
                            "
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :href="processo.data.volume_caixa.caixa.prateleira.estante.sala.andar.predio.links.view"
                            :valor="
                                processo.data.volume_caixa.caixa.prateleira.estante.sala.andar
                                    .predio.nome
                            "
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :href="processo.data.volume_caixa.caixa.prateleira.estante.sala.andar.links.view"
                            :valor="
                                new Andar(
                                    processo.data.volume_caixa.caixa.prateleira.estante.sala.andar
                                ).numeroExibicao()
                            "
                            icone="layers"
                        />

                        <ChaveValor
                            :chave="__('Sala')"
                            :href="processo.data.volume_caixa.caixa.prateleira.estante.sala.links.view"
                            :valor="processo.data.volume_caixa.caixa.prateleira.estante.sala.numero"
                            icone="door-closed"
                        />

                        <ChaveValor
                            :chave="__('Estante')"
                            :href="processo.data.volume_caixa.caixa.prateleira.estante.links.view"
                            :valor="processo.data.volume_caixa.caixa.prateleira.estante.numero"
                            icone="bookshelf"
                        />

                        <ChaveValor
                            :chave="__('Prateleira')"
                            :href="processo.data.volume_caixa.caixa.prateleira.links.view"
                            :valor="processo.data.volume_caixa.caixa.prateleira.numero"
                            icone="list-nested"
                        />

                        <ChaveValor
                            :chave="__('Caixa')"
                            :href="processo.data.volume_caixa.caixa.links.view"
                            :valor="new Caixa(processo.data.volume_caixa.caixa).numeroExibicao()"
                            icone="box2"
                        />

                        <ChaveValor
                            :chave="__('Volume da caixa')"
                            :href="processo.data.volume_caixa.links.view"
                            :valor="processo.data.volume_caixa.numero"
                            icone="box2"
                        />

                        <TextInput
                            v-model="form.processo_pai_numero"
                            :disabled="!modoEdicao"
                            :erro="form.errors.processo_pai_numero"
                            :label="__('Processo pai')"
                            :mascara="mascaraCNJ"
                            :maxlength="25"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="journal-bookmark"
                        />

                        <TextInput
                            v-model="form.numero"
                            :disabled="!modoEdicao"
                            :erro="form.errors.numero"
                            :label="__('Processo')"
                            :mascara="mascaraCNJ"
                            :maxlength="25"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="journal-bookmark"
                            required
                        />

                        <TextInput
                            v-model="form.numero_antigo"
                            :disabled="!modoEdicao"
                            :erro="form.errors.numero_antigo"
                            :label="__('Número antigo')"
                            :maxlength="25"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="journal-bookmark"
                        />

                        <TextInput
                            v-model="form.arquivado_em"
                            :disabled="!modoEdicao"
                            :erro="form.errors.arquivado_em"
                            :label="__('Data de arquivamento')"
                            :maxlength="10"
                            autocomplete="off"
                            icone="calendar-event"
                            mascara="##-##-####"
                            placeholder="dd-mm-aaaa"
                            required
                        />

                        <NumeroInput
                            v-model="form.qtd_volumes"
                            :disabled="!modoEdicao"
                            :erro="form.errors.qtd_volumes"
                            :label="__('Volumes')"
                            :max="9999"
                            :min="1"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="journals"
                            required
                        />

                        <ChaveValor
                            :chave="__('Guarda permanente')"
                            :valor="processo.data.guarda_permanente"
                            icone="safe"
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :disabled="!modoEdicao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre o processo')"
                        icone="blockquote-left"
                    />

                    <div
                        v-if="processo.data.links.update"
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
                    <CheckBox v-model="elementosVisiveis.processo" :label="__('Processo')" />

                    <CheckBox
                        v-model="elementosVisiveis.numeroAntigo"
                        :label="__('Número antigo')"
                    />

                    <CheckBox v-model="elementosVisiveis.arquivadoEm" :label="__('Arquivado em')" />

                    <CheckBox v-model="elementosVisiveis.guardaPermanente" :label="__('GP')" />

                    <CheckBox v-model="elementosVisiveis.qtdVolumes" :label="__('Volumes')" />

                    <CheckBox
                        v-model="elementosVisiveis.processosFilho"
                        :label="__('Qtd proc filho')"
                    />

                    <CheckBox
                        v-model="elementosVisiveis.solicitacoes"
                        :label="__('Qtd solicitações')"
                    />

                    <CheckBox v-model="elementosVisiveis.acao" :label="__('Ações')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
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

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="processos_filho.data.length">
                        <Row v-for="processo in processos_filho.data" :key="processo.id">
                            <Cell v-show="elementosVisiveis.processo">
                                <Clipboard :copiavel="processo.numero" />
                            </Cell>

                            <Cell v-show="elementosVisiveis.numeroAntigo">
                                <Clipboard
                                    v-if="processo.numero_antigo"
                                    :copiavel="processo.numero_antigo"
                                />
                            </Cell>

                            <Cell v-show="elementosVisiveis.arquivadoEm">{{
                                processo.arquivado_em
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.guardaPermanente">{{
                                processo.guarda_permanente
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.qtdVolumes">{{
                                processo.qtd_volumes
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.processosFilho">{{
                                processo.processos_filho_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.solicitacoes">{{
                                processo.solicitacoes_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
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
                                                processo.numero
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

            <Paginacao v-if="processos_filho.meta.last_page > 1" :meta="processos_filho.meta" />
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
