<!--
    View para visualização individual das características do processo.
    Idealizada para usuários com permissão de visualização do processo.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Andar from '@/Models/Andar';
import Caixa from '@/Models/Caixa';
import Lotacao from '@/Models/Lotacao';
import Processo from '@/Models/Processo';
import Usuario from '@/Models/Usuario';
import Alerta from '@/Shared/Containers/Alerta.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import Pesquisa from '@/Shared/Forms/Pesquisa.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import Preferencia from '@/Shared/Misc/Preferencia.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { Inertia } from '@inertiajs/inertia';
import { usePage } from '@inertiajs/inertia-vue3';
import { useLocalStorage } from '@vueuse/core';
import { first, isEmpty, upperFirst } from 'lodash';
import { computed, ref } from 'vue';

const props = defineProps({
    processo: { type: Object },
    links: { type: Object },
});

const __ = useTranslationsStore().__;

const termo = ref('');

const filtrar = () => {
    Inertia.post(
        props.links.search,
        { termo: termo.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const elementosVisiveis = useLocalStorage(usePage().component.value, {
    processo: true,
    localizacao: true,
    solicitacao: true,
});

const solicitacao_ativa = computed(() => first(props.processo.data.solicitacao_ativa));
</script>

<template>
    <Pagina :titulo="__('Processo detalhado')">
        <div class="space-y-3">
            <form @submit.prevent="filtrar">
                <Pesquisa v-model="termo" maxlength="25" />
            </form>

            <Preferencia v-if="!isEmpty(processo.data)">
                <CheckBox v-model:checked="elementosVisiveis.processo" :label="__('Processo')" />

                <CheckBox
                    v-model:checked="elementosVisiveis.localizacao"
                    :label="__('Localização')"
                />

                <CheckBox
                    v-model:checked="elementosVisiveis.solicitacao"
                    :label="__('Solicitação')"
                />
            </Preferencia>

            <Alerta v-if="!isEmpty(processo.data) && solicitacao_ativa?.entregue_em">
                <p>{{ __('Processo encontra-se fora do arquivo') }}</p>
            </Alerta>
        </div>

        <Transition
            v-if="!isEmpty(processo.data)"
            enter-from-class="opacity-0 scale-50"
            enter-to-class="opacity-100 scale-100"
            enter-active-class="transition duration-300 transform-gpu"
            leave-active-class="transition duration-200 transform-gpu"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-50"
        >
            <Container v-show="elementosVisiveis.processo" class="space-y-6">
                <h3 class="text-center text-3xl font-bold">{{ __('Processo') }}</h3>

                <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-3">
                    <ChaveValor
                        :chave="__('Número')"
                        :valor="processo.data.numero"
                        icone="journal-bookmark"
                    />

                    <ChaveValor
                        :chave="__('Número antigo')"
                        :valor="processo.data.numero_antigo"
                        icone="journal-bookmark"
                    />

                    <ChaveValor
                        :chave="__('Data de arquivamento')"
                        :valor="processo.data.arquivado_em"
                        icone="calendar-event"
                    />

                    <ChaveValor
                        :chave="__('Guarda permanente')"
                        :valor="processo.data.guarda_permanente"
                        icone="safe"
                    />

                    <ChaveValor
                        :chave="__('Qtd volumes')"
                        :valor="processo.data.qtd_volumes"
                        icone="journals"
                    />

                    <ChaveValor
                        :chave="__('Processo pai')"
                        :valor="processo.data.processo_pai?.numero"
                        icone="journal-bookmark"
                    />
                </div>

                <ChaveValor
                    :chave="__('Descrição')"
                    :valor="processo.data.descricao"
                    icone="blockquote-left"
                />
            </Container>
        </Transition>

        <Transition
            v-if="!isEmpty(processo.data)"
            enter-from-class="opacity-0 scale-50"
            enter-to-class="opacity-100 scale-100"
            enter-active-class="transition duration-300 transform-gpu"
            leave-active-class="transition duration-200 transform-gpu"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-50"
        >
            <Container v-show="elementosVisiveis.localizacao" class="space-y-6">
                <h3 class="text-center text-3xl font-bold">{{ __('Localização') }}</h3>

                <ChaveValor
                    :chave="__('Localização atual')"
                    :valor="new Processo(processo.data).localizacao()"
                    icone="geo-alt"
                />

                <div class="space-y-3">
                    <h6 class="text-center font-bold">{{ __('Local de arquivamento') }}</h6>

                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-3">
                        <ChaveValor
                            :chave="__('Localidade')"
                            :valor="
                                processo.data.volume_caixa.caixa.prateleira.estante.sala.andar
                                    .predio.localidade.nome
                            "
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :valor="
                                processo.data.volume_caixa.caixa.prateleira.estante.sala.andar
                                    .predio.nome
                            "
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :valor="
                                new Andar(
                                    processo.data.volume_caixa.caixa.prateleira.estante.sala.andar
                                ).numeroExibicao()
                            "
                            icone="layers"
                        />

                        <ChaveValor
                            :chave="__('Sala')"
                            :valor="processo.data.volume_caixa.caixa.prateleira.estante.sala.numero"
                            icone="door-closed"
                        />

                        <ChaveValor
                            :chave="__('Estante')"
                            :valor="processo.data.volume_caixa.caixa.prateleira.estante.numero"
                            icone="bookshelf"
                        />

                        <ChaveValor
                            :chave="__('Prateleira')"
                            :valor="processo.data.volume_caixa.caixa.prateleira.numero"
                            icone="list-nested"
                        />

                        <ChaveValor
                            :chave="__('Caixa')"
                            :valor="new Caixa(processo.data.volume_caixa.caixa).numeroExibicao()"
                            icone="box2"
                        />

                        <ChaveValor
                            :chave="__('Volume da caixa')"
                            :valor="processo.data.volume_caixa.numero"
                            icone="boxes"
                        />
                    </div>
                </div>
            </Container>
        </Transition>

        <Transition
            v-if="!isEmpty(processo.data)"
            enter-from-class="opacity-0 scale-50"
            enter-to-class="opacity-100 scale-100"
            enter-active-class="transition duration-300 transform-gpu"
            leave-active-class="transition duration-200 transform-gpu"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-50"
        >
            <Container v-show="elementosVisiveis.solicitacao" class="space-y-6">
                <h3 class="text-center text-3xl font-bold">{{ __('Solicitação') }}</h3>

                <div
                    v-if="solicitacao_ativa"
                    class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-3"
                >
                    <ChaveValor
                        :chave="__('Status')"
                        :valor="upperFirst(solicitacao_ativa.status)"
                        icone="cart"
                    />

                    <ChaveValor
                        :chave="__('Solicitada em')"
                        :valor="solicitacao_ativa.solicitada_em"
                        icone="calendar-event"
                    />

                    <ChaveValor
                        :chave="__('Solicitante')"
                        :valor="new Usuario(solicitacao_ativa.solicitante).nomeExibicao()"
                        icone="person"
                    />

                    <ChaveValor
                        v-if="solicitacao_ativa.entregue_em"
                        :chave="__('Entregue em')"
                        :valor="solicitacao_ativa.entregue_em"
                        icone="calendar-event"
                    />

                    <ChaveValor
                        v-if="solicitacao_ativa.remetente"
                        :chave="__('Remetente')"
                        :valor="new Usuario(solicitacao_ativa.remetente).nomeExibicao()"
                        icone="person"
                    />

                    <ChaveValor
                        v-if="solicitacao_ativa.recebedor"
                        :chave="__('Recebedor')"
                        :valor="new Usuario(solicitacao_ativa.recebedor).nomeExibicao()"
                        icone="person"
                    />

                    <ChaveValor
                        v-if="solicitacao_ativa.lotacao_destinataria"
                        :chave="__('Lotação destinatária')"
                        :valor="new Lotacao(solicitacao_ativa.lotacao_destinataria).nomeExibicao()"
                        icone="person"
                    />
                </div>

                <p v-else class="text-center">{{ __('Processo sem solicitação ativa') }}</p>
            </Container>
        </Transition>
    </Pagina>
</template>
