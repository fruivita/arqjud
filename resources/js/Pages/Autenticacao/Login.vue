<!--
    View de login.

    Esta view não usa o layout padrão da aplicação. Além disso, é renderizada
    apenas para usuários não autenticados.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
    @see https://www.chromium.org/developers/design-documents/create-amazing-password-forms
 -->
<script>
export default {
    layout: null,
};
</script>

<script setup>
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import { useDadosEstaticosStore } from '@/Stores/DadosEstaticosStore';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import { useDark } from '@vueuse/core';

const __ = useTranslationsStore().__;

useDark({ storageKey: 'tema' });

const form = useForm({
    matricula: '',
    password: '',
});

const autenticar = () => {
    form.post(route('login'));
};
</script>

<template>
    <div
        class="flex min-h-screen transform-gpu items-center justify-center bg-primaria-50 text-xl text-primaria-900 transition duration-300 dark:bg-secundaria-900 dark:text-secundaria-50"
    >
        <article
            class="flex flex-col items-center space-y-12 px-6 py-10 shadow-lg shadow-secundaria-500 dark:shadow-primaria-500"
        >
            <h1
                class="flex h-32 w-32 flex-col items-center justify-center rounded-full bg-primaria-500 font-extrabold text-primaria-50"
            >
                <span dusk="app-name">{{ useDadosEstaticosStore().appNome }}</span>

                <span dusk="orgao-sigla">{{ useDadosEstaticosStore().orgaoSigla }}</span>
            </h1>

            <form @submit.prevent="autenticar" dusk="login-form">
                <div class="space-y-6">
                    <TextInput
                        v-model="form.matricula"
                        :erro="form.errors.matricula"
                        :label="__('Matrícula')"
                        :maxlength="20"
                        :placeholder="__('ESXXXXX')"
                        autocomplete="matricula"
                        dusk="matricula"
                        icone="person"
                        required
                    />

                    <TextInput
                        v-model="form.password"
                        :erro="form.errors.password"
                        :label="__('Senha de rede')"
                        :maxlength="50"
                        autocomplete="current-password"
                        dusk="password"
                        icone="key"
                        type="password"
                        required
                    />

                    <ButtonText
                        :texto="__('Entrar')"
                        class="w-full"
                        dusk="autenticar"
                        icone="box-arrow-in-right"
                        type="submit"
                    />
                </div>
            </form>
        </article>
    </div>
</template>
