<!--
    View para criação da caixa.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import { numeroAndar } from '@/Helpers/Andar';
import { numeroCaixa } from '@/Helpers/Caixa';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import DropDown from '@/Shared/Forms/DropDown.vue';
import NumeroInput from '@/Shared/Forms/NumeroInput.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import UltimoCadastro from '@/Shared/Misc/UltimoCadastro.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import { isEmpty } from 'lodash';

const props = defineProps({
    ultima_insercao: { type: Object },
    prateleira: { type: Object }, // objeto pai do que será criado
    localidades: { type: Object }, // opções para localidade de criação da caixa
    tipos_processo: { type: Object }, // opções para o tipo de processo
});

const __ = useTranslationsStore().__;

const form = useForm({
    numero: '',
    ano: '',
    guarda_permanente: false,
    descricao: '',
    localidade_criadora_id: '',
    tipo_processo_id: '',
});

const cadastrar = () => {
    form.post(props.prateleira.data.links.caixa.store, {
        onSuccess: () => {
            form.reset();
            flash();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Pagina :titulo="__('Nova caixa')">
        <Container>
            <form @submit.prevent="cadastrar">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                        <ChaveValor
                            :chave="__('Localidade')"
                            :href="prateleira.data.estante.sala.andar.predio.localidade.links.view"
                            :valor="prateleira.data.estante.sala.andar.predio.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :href="prateleira.data.estante.sala.andar.predio.links.view"
                            :valor="prateleira.data.estante.sala.andar.predio.nome"
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :href="prateleira.data.estante.sala.andar.links.view"
                            :valor="numeroAndar(prateleira.data.estante.sala.andar)"
                            icone="layers"
                        />

                        <ChaveValor
                            :chave="__('Sala')"
                            :href="prateleira.data.estante.sala.links.view"
                            :valor="prateleira.data.estante.sala.numero"
                            icone="door-closed"
                        />

                        <ChaveValor
                            :chave="__('Estante')"
                            :href="prateleira.data.estante.links.view"
                            :valor="prateleira.data.estante.numero"
                            icone="bookshelf"
                        />

                        <ChaveValor
                            :chave="__('Prateleira')"
                            :href="prateleira.data.links.view"
                            :valor="prateleira.data.numero"
                            icone="list-nested"
                        />

                        <NumeroInput
                            v-model="form.numero"
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
                            :erro="form.errors.ano"
                            :label="__('Ano da caixa')"
                            :max="new Date().getFullYear()"
                            :min="1900"
                            :placeholder="__('aaaa')"
                            autocomplete="off"
                            icone="calendar-range"
                            required
                        />

                        <DropDown
                            v-model="form.localidade_criadora_id"
                            :erro="form.errors.localidade_criadora_id"
                            :label="__('Localidade criadora')"
                            :opcoes="localidades.data"
                            icone="pin-map"
                            labelOpcao="nome"
                            required
                        />

                        <DropDown
                            v-model="form.tipo_processo_id"
                            :erro="form.errors.tipo_processo_id"
                            :label="__('Tipo de processo')"
                            :opcoes="tipos_processo.data"
                            icone="card-list"
                            labelOpcao="nome"
                            required
                        />

                        <CheckBox
                            v-model:checked="form.guarda_permanente"
                            :label="__('Guarda Permanente')"
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre a caixa')"
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
                        : __('Caixa: :attribute', {
                              attribute: numeroCaixa(ultima_insercao.data),
                          })
                "
            />
        </Container>
    </Pagina>
</template>
