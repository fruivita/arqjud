<!--
    View para criação da prateleira.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import Andar from '@/Models/Andar';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import UltimoCadastro from '@/Shared/Misc/UltimoCadastro.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import { isEmpty } from 'lodash';

const props = defineProps({
    ultima_insercao: { type: Object },
    estante: { type: Object }, // objeto pai do que será criado
});

const __ = useTranslationsStore().__;

const form = useForm({
    numero: '',
    descricao: '',
});

const cadastrar = () => {
    form.post(props.estante.data.links.create_prateleira, {
        onSuccess: () => {
            form.reset();
            flash();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Pagina :titulo="__('Nova prateleira')">
        <Container>
            <form @submit.prevent="cadastrar">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                        <ChaveValor
                            :chave="__('Localidade')"
                            :href="estante.data.sala.andar.predio.localidade.links.view"
                            :valor="estante.data.sala.andar.predio.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :href="estante.data.sala.andar.predio.links.view"
                            :valor="estante.data.sala.andar.predio.nome"
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :href="estante.data.sala.andar.links.view"
                            :valor="new Andar(estante.data.sala.andar).numeroExibicao()"
                            icone="layers"
                        />

                        <ChaveValor
                            :chave="__('Sala')"
                            :href="estante.data.sala.links.view"
                            :valor="estante.data.sala.numero"
                            icone="door-closed"
                        />

                        <ChaveValor
                            :chave="__('Estante')"
                            :href="estante.data.links.view"
                            :valor="estante.data.numero"
                            icone="bookshelf"
                        />

                        <TextInput
                            v-model="form.numero"
                            :erro="form.errors.numero"
                            :label="__('Número da prateleira')"
                            :maxlength="50"
                            :placeholder="__('Ex.: 100, 100-F, ...')"
                            autocomplete="off"
                            icone="list-nested"
                            required
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre a prateleira')"
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
                        : __('Prateleira: :attribute', { attribute: ultima_insercao.data.numero })
                "
            />
        </Container>
    </Pagina>
</template>
