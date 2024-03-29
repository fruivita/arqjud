<!--
    View para criação do processo.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import { numeroAndar } from '@/Helpers/Andar';
import { gp, numeroCaixa } from '@/Helpers/Caixa';
import { mascaraCNJ } from '@/keys';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import NumeroInput from '@/Shared/Forms/NumeroInput.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import UltimoCadastro from '@/Shared/Misc/UltimoCadastro.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import { isEmpty } from 'lodash';

const props = defineProps({
    ultima_insercao: { type: Object },
    caixa: { type: Object }, // objeto pai do que será criado
});

const __ = useTranslationsStore().__;

const form = useForm({
    numero: '',
    numero_antigo: '',
    arquivado_em: '',
    vol_caixa_inicial: '',
    vol_caixa_final: '',
    qtd_volumes: '',
    descricao: '',
    processo_pai_numero: '',
});

const cadastrar = () => {
    form.post(props.caixa.data.links.processo.store, {
        onSuccess: () => {
            form.reset();
            flash();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Pagina :titulo="__('Novo processo')">
        <Container>
            <form @submit.prevent="cadastrar">
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
                            :chave="__('Caixa')"
                            :href="caixa.data.links.view"
                            :valor="numeroCaixa(caixa.data)"
                            icone="box2"
                        />

                        <ChaveValor
                            :chave="__('TIpo de processo')"
                            :href="caixa.data.tipo_processo.links.view"
                            :valor="caixa.data.tipo_processo.nome"
                            icone="card-list"
                        />

                        <TextInput
                            v-model="form.processo_pai_numero"
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
                            :erro="form.errors.numero_antigo"
                            :label="__('Número antigo')"
                            :maxlength="25"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="journal-bookmark"
                        />

                        <TextInput
                            v-model="form.arquivado_em"
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
                            :erro="form.errors.qtd_volumes"
                            :label="__('Qtd volumes')"
                            :max="9999"
                            :min="1"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="journals"
                            required
                        />

                        <NumeroInput
                            v-model="form.vol_caixa_inicial"
                            :erro="form.errors.vol_caixa_inicial"
                            :label="__('Vol. caixa inicial')"
                            :max="9999"
                            :min="1"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="folder"
                            required
                        />

                        <NumeroInput
                            v-model="form.vol_caixa_final"
                            :erro="form.errors.vol_caixa_final"
                            :label="__('Vol. caixa final')"
                            :max="9999"
                            :min="1"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="folder-fill"
                            required
                        />

                        <ChaveValor
                            :chave="__('Guarda permanente')"
                            :valor="gp(caixa.data)"
                            icone="safe"
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre o processo')"
                        icone="blockquote-left"
                    />

                    <div class="flex justify-end">
                        <ButtonText
                            :texto="__('Salvar')"
                            dusk="cadastrar"
                            especie="acao"
                            icone="save"
                            type="submit"
                        />
                    </div>
                </div>
            </form>
        </Container>

        <Container>
            <UltimoCadastro
                :href="ultima_insercao.data.links?.view"
                :texto="
                    isEmpty(ultima_insercao.data)
                        ? null
                        : __('Processo: :attribute', { attribute: ultima_insercao.data.numero })
                "
            />
        </Container>
    </Pagina>
</template>
