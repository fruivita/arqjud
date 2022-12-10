<!--
    View para criação da sala.

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
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import UltimoCadastro from '@/Shared/Misc/UltimoCadastro.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import { isEmpty } from 'lodash';

const props = defineProps({
    ultima_insercao: { type: Object },
    andar: { type: Object }, // objeto pai do que será criado
});

const __ = useTranslationsStore().__;

const form = useForm({
    numero: '',
    descricao: '',
    andar_id: props.andar.data.id,
});

const cadastrar = () => {
    form.post(props.andar.data.links.create_sala, {
        onSuccess: () => {
            form.reset();
            flash();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Pagina :titulo="__('Nova sala')">
        <Container>
            <form @submit.prevent="cadastrar">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                        <ChaveValor
                            :chave="__('Localidade')"
                            :valor="andar.data.predio.localidade.nome"
                            icone="pin-map"
                        />

                        <ChaveValor
                            :chave="__('Prédio')"
                            :valor="andar.data.predio.nome"
                            icone="buildings"
                        />

                        <ChaveValor
                            :chave="__('Andar')"
                            :valor="new Andar(andar.data).numeroExibicao()"
                            icone="layers"
                        />

                        <TextInput
                            v-model="form.numero"
                            :erro="form.errors.numero"
                            :label="__('Número da sala')"
                            :maxlength="50"
                            :placeholder="__('Ex.: 100, 100-F, ...')"
                            autocomplete="off"
                            icone="door-closed"
                            required
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre a sala')"
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
                        : __('Sala: :attribute', { attribute: ultima_insercao.data.numero })
                "
            />
        </Container>
    </Pagina>
</template>
