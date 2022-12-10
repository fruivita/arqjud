<!--
    View para visualização e edição da caixa.

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
import Caixa from '@/Models/Caixa';
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
    volumes_caixa: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    localidade_criadora_id: props.caixa.data.localidade_criadora_id,
    numero: props.caixa.data.numero,
    numero: props.caixa.data.numero,
    ano: props.caixa.data.ano,
    guarda_permanente: new Caixa(props.caixa.data).gp(),
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

const { ordenacoes, mudarOrdenacao } = useOrdenacao(props.volumes_caixa.meta.order);

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    volumeCaixa: true,
    processos: true,
    acao: true,
});

const colspan = computed(() => countElementosVisiveis(elementosVisiveis));

const perPage = ref(props.volumes_caixa.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.volumes_caixa.meta.path,
        pickBy(merge({ order: ordenacoes.value }, { per_page: perPage.value })),
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['volumes_caixa'],
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
                            :valor="caixa.data.prateleira.estante.sala.andar.predio.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :valor="caixa.data.prateleira.estante.sala.andar.predio.nome"
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :valor="
                                new Andar(caixa.data.prateleira.estante.sala.andar).numeroExibicao()
                            "
                            icone="layers"
                        />

                        <ChaveValor
                            :chave="__('Sala')"
                            :valor="caixa.data.prateleira.estante.sala.numero"
                            icone="door-closed"
                        />

                        <ChaveValor
                            :chave="__('Estante')"
                            :valor="caixa.data.prateleira.estante.numero"
                            icone="bookshelf"
                        />

                        <ChaveValor
                            :chave="__('Prateleira')"
                            :valor="caixa.data.prateleira.numero"
                            icone="list-nested"
                        />

                        <ChaveValor
                            :chave="__('Localidade criadora')"
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
                            v-model="form.guarda_permanente"
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
                    'md:justify-between': caixa.data.links.create_volume,
                    'md:justify-end': !caixa.data.links.create_volume,
                }"
                class="flex flex-col space-y-3 md:flex-row md:items-start"
            >
                <InertiaButtonLink
                    v-if="caixa.data.links.create_volume"
                    :href="caixa.data.links.create_volume"
                    :texto="__('Novo volume')"
                    icone="plus-circle"
                />

                <Preferencia>
                    <CheckBox v-model="elementosVisiveis.volumeCaixa" :label="__('Volume')" />

                    <CheckBox v-model="elementosVisiveis.processos" :label="__('Qtd processos')" />

                    <CheckBox v-model="elementosVisiveis.acao" :label="__('Ações')" />
                </Preferencia>
            </div>

            <Tabela>
                <template #header>
                    <HeadingOrdenavel
                        v-show="elementosVisiveis.volumeCaixa"
                        :ordenacao="ordenacoes.numero"
                        :texto="__('Volume')"
                        @ordenar="(direcao) => mudarOrdenacao('numero', direcao)"
                    />

                    <HeadingOrdenavel
                        v-show="elementosVisiveis.processos"
                        :ordenacao="ordenacoes.processos_count"
                        :texto="__('Qtd processos')"
                        @ordenar="(direcao) => mudarOrdenacao('processos_count', direcao)"
                    />

                    <Heading v-show="elementosVisiveis.acao" :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="volumes_caixa.data.length">
                        <Row v-for="volume in volumes_caixa.data" :key="volume.id">
                            <Cell v-show="elementosVisiveis.volumeCaixa">{{ volume.numero }}</Cell>

                            <Cell v-show="elementosVisiveis.processos">{{
                                volume.processos_count
                            }}</Cell>

                            <Cell v-show="elementosVisiveis.acao" class="w-10">
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="volume.links.view"
                                        :href="volume.links.view"
                                        icone="eye"
                                    />

                                    <ButtonIcone
                                        v-if="volume.links.delete"
                                        @click="
                                            confirmarExclusao(volume.links.delete, volume.numero)
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

            <Paginacao v-if="volumes_caixa.meta.last_page > 1" :meta="volumes_caixa.meta" />
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
