<!--
    View para criação do andar.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/useFlash';
import Andar from '@/Models/Andar';
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
    predio: { type: Object }, // objeto pai do que será criado
});

const __ = useTranslationsStore().__;

const form = useForm({
    numero: '',
    apelido: '',
    descricao: '',
});

const cadastrar = () => {
    form.post(props.predio.data.links.create_andar, {
        onSuccess: () => {
            form.reset();
            flash();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Pagina :titulo="__('Novo andar')">
        <Container>
            <form @submit.prevent="cadastrar">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                        <ChaveValor
                            :chave="__('Localidade')"
                            :href="predio.data.localidade.links.view"
                            :valor="predio.data.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :href="predio.data.links.view"
                            :valor="predio.data.nome"
                            icone="buildings"
                        />

                        <NumeroInput
                            v-model="form.numero"
                            :erro="form.errors.numero"
                            :label="__('Andar')"
                            :max="300"
                            :min="-100"
                            :placeholder="__('Número do andar')"
                            autocomplete="off"
                            icone="layers"
                            required
                        />

                        <TextInput
                            v-model="form.apelido"
                            :erro="form.errors.apelido"
                            :label="__('Apelido')"
                            :maxlength="100"
                            :placeholder="__('Garagem, G1, Térreo, 10º, ...')"
                            autocomplete="off"
                            icone="symmetry-vertical"
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre o andar')"
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
                        : __('Andar: :attribute', {
                              attribute: new Andar(ultima_insercao.data).numeroExibicao(),
                          })
                "
            />
        </Container>
    </Pagina>
</template>
