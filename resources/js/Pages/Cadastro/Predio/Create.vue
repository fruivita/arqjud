<!--
    View para criação do prédio.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/useFlash';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
    ultima_insercao: { type: Object },
    localidade: { type: Object }, // objeto pai do que será criado
});

const __ = useTranslationsStore().__;

const form = useForm({
    nome: '',
    descricao: '',
    localidade_id: props.localidade.data.id,
});

const cadastrar = () => {
    form.post(props.localidade.data.links.create_predio, {
        onSuccess: () => {
            form.reset();
            flash();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Pagina :titulo="__('Novo prédio')">
        <Container>
            <form @submit.prevent="cadastrar">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                        <ChaveValor
                            :chave="__('Localidade')"
                            :valor="localidade.data.nome"
                            icone="pin-map"
                        />

                        <TextInput
                            v-model="form.nome"
                            :erro="form.errors.nome"
                            :label="__('Prédio')"
                            :maxlength="100"
                            :placeholder="__('Nome do prédio')"
                            autocomplete="off"
                            icone="buildings"
                            required
                        />
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre o prédio')"
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

        <Container v-if="ultima_insercao">
            <div>
                <div class="text-sm text-primaria-700 dark:text-secundaria-300">
                    {{ __('Último item cadastrado:') }}
                </div>

                <component
                    :class="{
                        'underline hover:opacity-80': ultima_insercao.data.links.view,
                    }"
                    :href="ultima_insercao.data.links.view"
                    :is="ultima_insercao.data.links.view ? 'InertiaLink' : 'span'"
                    class="font-bold"
                    v-html="__('Prédio: :attribute', { attribute: ultima_insercao.data.nome })"
                />
            </div>
        </Container>
    </Pagina>
</template>
