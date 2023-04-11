<!--
    View para listagem (com filtros) dos processos.

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
import { gp } from '@/Helpers/Caixa';
import { gp as processoGP } from '@/Helpers/Processo';
import { perPageKey, updatePerPageKey } from '@/keys';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import Pesquisa from '@/Shared/Forms/Pesquisa.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
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
import { usePage } from '@inertiajs/inertia-vue3';
import { useLocalStorage } from '@vueuse/core';
import { merge, pickBy } from 'lodash';
import { computed, provide, readonly, ref, watch } from 'vue';

const props = defineProps({
    processos: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref(props.processos.meta.termo ?? '');

const { confirmarExclusao, excluir, titulo } = useExclusao();

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.processos.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    processo: true,
    numeroAntigo: true,
    arquivadoEm: true,
    guardaPermanente: true,
    qtdVolumes: true,
    volCaixaInicial: true,
    volCaixaFinal: true,
    processosFilho: true,
    solicitacoes: true,
    localidade: true,
    predio: true,
    andarNumero: true,
    andarApelido: true,
    sala: true,
    estante: true,
    prateleira: true,
    caixa: true,
    caixaAno: true,
    caixaGuardaPermanente: true,
    caixaComplemento: true,
    caixaLocalidadeCriadora: true,
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
        pickBy(
            merge({ termo: termo.value }, { order: ordenacoes.value }, { per_page: perPage.value })
        ),
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
</script>

<template>
    <Pagina :titulo="__('Processos')">
        <form @submit.prevent="filtrar">
            <Pesquisa v-model="termo" />
        </form>

        <Container class="space-y-3">
            <Preferencia>
                <CheckBox v-model:checked="elementosVisiveis.acao" :label="__('Ações')" />

                <CheckBox v-model:checked="elementosVisiveis.processo" :label="__('Processo')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.numeroAntigo"
                    :label="__('Número antigo')"
                />

                <CheckBox
                    v-model:checked="elementosVisiveis.arquivadoEm"
                    :label="__('Arquivado em')"
                />

                <CheckBox v-model:checked="elementosVisiveis.guardaPermanente" :label="__('GP')" />

                <CheckBox v-model:checked="elementosVisiveis.qtdVolumes" :label="__('Volumes')" />

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

                <CheckBox
                    v-model:checked="elementosVisiveis.localidade"
                    :label="__('Localidade')"
                />

                <CheckBox v-model:checked="elementosVisiveis.predio" :label="__('Prédio')" />

                <CheckBox v-model:checked="elementosVisiveis.andarNumero" :label="__('Andar')" />

                <CheckBox v-model:checked="elementosVisiveis.andarApelido" :label="__('Apelido')" />

                <CheckBox v-model:checked="elementosVisiveis.sala" :label="__('Sala')" />

                <CheckBox v-model:checked="elementosVisiveis.estante" :label="__('Estante')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.prateleira"
                    :label="__('Prateleira')"
                />

                <CheckBox v-model:checked="elementosVisiveis.caixa" :label="__('Caixa')" />

                <CheckBox v-model:checked="elementosVisiveis.caixaAno" :label="__('Ano')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.caixaGuardaPermanente"
                    :label="__('GP (caixa)')"
                />

                <CheckBox
                    v-model:checked="elementosVisiveis.caixaLocalidadeCriadora"
                    :label="__('Localidade criadora')"
                />

                <CheckBox
                    v-model:checked="elementosVisiveis.caixaComplemento"
                    :label="__('Complemento')"
                />
            </Preferencia>

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

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.localidade"
                        :ordenacao="ordenacoes.localidade_pai_nome"
                        :texto="__('Localidade')"
                        @ordenar="(direcao) => mudarOrdenacao('localidade_pai_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.predio"
                        :ordenacao="ordenacoes.predio_pai_nome"
                        :texto="__('Prédio')"
                        @ordenar="(direcao) => mudarOrdenacao('predio_pai_nome', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.andarNumero"
                        :ordenacao="ordenacoes.andar_pai_numero"
                        :texto="__('Andar')"
                        @ordenar="(direcao) => mudarOrdenacao('andar_pai_numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.andarApelido"
                        :ordenacao="ordenacoes.andar_pai_apelido"
                        :texto="__('Apelido')"
                        @ordenar="(direcao) => mudarOrdenacao('andar_pai_apelido', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.sala"
                        :ordenacao="ordenacoes.sala_pai_numero"
                        :texto="__('Sala')"
                        @ordenar="(direcao) => mudarOrdenacao('sala_pai_numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.estante"
                        :ordenacao="ordenacoes.estante_pai_numero"
                        :texto="__('Estante')"
                        @ordenar="(direcao) => mudarOrdenacao('estante_pai_numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.prateleira"
                        :ordenacao="ordenacoes.prateleira_pai_numero"
                        :texto="__('Prateleira')"
                        @ordenar="(direcao) => mudarOrdenacao('prateleira_pai_numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.caixa"
                        :ordenacao="ordenacoes.caixa_pai_numero"
                        :texto="__('Caixa')"
                        @ordenar="(direcao) => mudarOrdenacao('caixa_pai_numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.caixaAno"
                        :ordenacao="ordenacoes.caixa_pai_ano"
                        :texto="__('Ano')"
                        @ordenar="(direcao) => mudarOrdenacao('caixa_pai_ano', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.caixaGuardaPermanente"
                        :ordenacao="ordenacoes.caixa_pai_guarda_permanente"
                        :texto="__('GP (caixa)')"
                        @ordenar="
                            (direcao) => mudarOrdenacao('caixa_pai_guarda_permanente', direcao)
                        "
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.caixaLocalidadeCriadora"
                        :ordenacao="ordenacoes.caixa_pai_localidade_criadora_nome"
                        :texto="__('Localidade criadora')"
                        @ordenar="
                            (direcao) =>
                                mudarOrdenacao('caixa_pai_localidade_criadora_nome', direcao)
                        "
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.caixaComplemento"
                        :ordenacao="ordenacoes.caixa_pai_complemento"
                        :texto="__('Complemento')"
                        @ordenar="(direcao) => mudarOrdenacao('caixa_pai_complemento', direcao)"
                    />
                </template>

                <template #body>
                    <template v-if="processos.data.length">
                        <Row v-for="processo in processos.data" :key="processo.id">
                            <Cell v-show="elementosVisiveis.acao" fixo>
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

                            <Cell v-show="elementosVisiveis.arquivadoEm">
                                {{ processo.arquivado_em }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.guardaPermanente">
                                {{ processoGP(processo) }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.qtdVolumes">
                                {{ processo.qtd_volumes }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.volCaixaInicial">
                                {{ processo.vol_caixa_inicial }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.volCaixaFinal">
                                {{ processo.vol_caixa_final }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.processosFilho">
                                {{ processo.processos_filho_count }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.solicitacoes">
                                {{ processo.solicitacoes_count }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.localidade">
                                {{
                                    processo.caixa.prateleira.estante.sala.andar.predio.localidade
                                        .nome
                                }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.predio">
                                {{ processo.caixa.prateleira.estante.sala.andar.predio.nome }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.andarNumero">
                                {{ processo.caixa.prateleira.estante.sala.andar.numero }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.andarApelido">
                                {{ processo.caixa.prateleira.estante.sala.andar.apelido }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.sala">
                                {{ processo.caixa.prateleira.estante.sala.numero }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.estante">
                                {{ processo.caixa.prateleira.estante.numero }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.prateleira">
                                {{ processo.caixa.prateleira.numero }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.caixa">
                                {{ processo.caixa.numero }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.caixaAno">
                                {{ processo.caixa.ano }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.caixaGuardaPermanente">
                                {{ gp(processo.caixa) }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.caixaLocalidadeCriadora">
                                {{ processo.caixa.localidade_criadora.nome }}
                            </Cell>

                            <Cell v-show="elementosVisiveis.caixaComplemento">
                                {{ processo.caixa.complemento }}
                            </Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell :colspan="colspan">{{ __('Nenhum registro encontrado') }}</Cell>
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
