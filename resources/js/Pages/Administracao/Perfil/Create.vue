<!--
    View para criação do Perfil.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import NumeroInput from '@/Shared/Forms/NumeroInput.vue';
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import UltimoCadastro from '@/Shared/Misc/UltimoCadastro.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import { isEmpty } from 'lodash';

const props = defineProps({
    ultima_insercao: { type: Object },
    links: { type: Object },
});

const __ = useTranslationsStore().__;

const form = useForm({
    nome: '',
    poder: '',
    descricao: '',
});

const cadastrar = () => {
    form.post(props.links.store, {
        onSuccess: () => {
            form.reset();
            flash();
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Pagina :titulo="__('Novo perfil')">
        <Container>
            <form @submit.prevent="cadastrar">
                <div class="space-y-6">
                    <TextInput
                        v-model="form.nome"
                        :erro="form.errors.nome"
                        :label="__('Perfil')"
                        :maxlength="50"
                        :placeholder="__('Nome do perfil')"
                        autocomplete="off"
                        icone="award"
                        required
                    />

                    <div>
                        <NumeroInput
                            v-model="form.poder"
                            :erro="form.errors.poder"
                            :label="__('Poder')"
                            :max="8999"
                            :min="1001"
                            :placeholder="__('Apenas números')"
                            autocomplete="off"
                            icone="p-circle"
                            required
                        />

                        <p class="text-sm">
                            {{ __('Usado para estabelecer a hierarquia entre os perfis.') }}
                        </p>
                    </div>

                    <TextAreaInput
                        v-model="form.descricao"
                        :erro="form.errors.descricao"
                        :label="__('Descrição')"
                        :maxlength="255"
                        :placeholder="__('Sobre o perfil')"
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
                        : __('Perfil: :attribute', { attribute: ultima_insercao.data.nome })
                "
            />
        </Container>
    </Pagina>
</template>
