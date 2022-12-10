<!--
    View para criação da caixa.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/useFlash';
import Andar from '@/Models/Andar';
import Caixa from '@/Models/Caixa';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import CheckBox from '@/Shared/Forms/CheckBox.vue';
import DropDown from '@/Shared/Forms/DropDown.vue';
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
    prateleira: { type: Object }, // objeto pai do que será criado
    localidades: { type: Object }, // opções para localidade de criação da caixa
});

const __ = useTranslationsStore().__;

const form = useForm({
    numero: '',
    ano: '',
    guarda_permanente: false,
    complemento: '',
    descricao: '',
    localidade_criadora_id: '',
    prateleira_id: props.prateleira.data.id,
});

const cadastrar = () => {
    form.post(props.prateleira.data.links.create_caixa, {
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
                            :valor="prateleira.data.estante.sala.andar.predio.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :valor="prateleira.data.estante.sala.andar.predio.nome"
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :valor="new Andar(prateleira.data.estante.sala.andar).numeroExibicao()"
                            icone="layers"
                        />

                        <ChaveValor
                            :chave="__('Sala')"
                            :valor="prateleira.data.estante.sala.numero"
                            icone="door-closed"
                        />

                        <ChaveValor
                            :chave="__('Estante')"
                            :valor="prateleira.data.estante.numero"
                            icone="bookshelf"
                        />

                        <ChaveValor
                            :chave="__('Prateleira')"
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

                        <TextInput
                            v-model="form.complemento"
                            :erro="form.errors.complemento"
                            :label="__('Complemento do número')"
                            :maxlength="50"
                            :placeholder="__('Ex.: Cri, Civ, ...')"
                            autocomplete="off"
                            icone="quote"
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

                        <CheckBox
                            v-model="form.guarda_permanente"
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
                              attribute: new Caixa(ultima_insercao.data).numeroExibicao(),
                          })
                "
            />
        </Container>
    </Pagina>
</template>
