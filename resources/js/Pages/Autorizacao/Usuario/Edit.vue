<!--
    View para visualização e edição do usuário.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
    @link https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import Lotacao from '@/Models/Lotacao';
import Usuario from '@/Models/Usuario';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Alerta from '@/Shared/Containers/Alerta.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import DropDown from '@/Shared/Forms/DropDown.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    perfis: { type: Object },
    usuario: { type: Object },
});

const __ = useTranslationsStore().__;

const modoEdicao = ref(false);

const form = useForm({
    perfil_id: props.usuario.data.perfil_id ?? '',
});

const atualizar = () => {
    form.patch(props.usuario.data.links.update, {
        preserveScroll: true,
        onSuccess: () => {
            flash();
            modoEdicao.value = false;
        },
    });
};

const cancelarEdicao = () => {
    form.reset();
    form.clearErrors();
    modoEdicao.value = false;
};

const tituloPagina = computed(() =>
    modoEdicao.value === true
        ? __(':attribute: Modo Edição', { attribute: 'Usuário' })
        : __(':attribute: Modo Visualização', { attribute: 'Usuário' })
);

const completo = computed(() => props.usuario.data.status === __('completo'));
</script>

<template>
    <Pagina :titulo="tituloPagina">
        <Container class="space-y-3">
            <form @submit.prevent="atualizar">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                        <ChaveValor
                            :chave="__('Cadastro')"
                            :class="{ 'text-red-500': !completo }"
                            :valor="usuario.data.status"
                            :icone="completo ? 'hand-thumbs-up' : 'hand-thumbs-down'"
                        />

                        <ChaveValor :chave="__('Nome')" :valor="usuario.data.nome" icone="pen" />

                        <ChaveValor
                            :chave="__('Matrícula')"
                            :valor="usuario.data.matricula"
                            icone="tag"
                        />

                        <ChaveValor
                            :chave="__('Email')"
                            :valor="usuario.data.email"
                            icone="envelope"
                        />

                        <ChaveValor
                            :chave="__('Último login')"
                            :valor="new Usuario(usuario.data).ultimoLogin()"
                            icone="clock"
                        />

                        <ChaveValor
                            :chave="__('Lotação')"
                            :valor="new Lotacao(usuario.data.lotacao).nomeExibicao()"
                            icone="building"
                        />

                        <ChaveValor
                            :chave="__('Cargo')"
                            :valor="usuario.data.cargo?.nome"
                            icone="person-vcard"
                        />

                        <ChaveValor
                            :chave="__('Função de confiança')"
                            :valor="usuario.data.funcao?.nome"
                            icone="emoji-sunglasses"
                        />

                        <DropDown
                            v-model="form.perfil_id"
                            :disabled="!modoEdicao"
                            :erro="form.errors.perfil_id"
                            :label="__('Perfil')"
                            :opcoes="perfis.data"
                            icone="award"
                            labelOpcao="nome"
                            required
                        />
                    </div>

                    <div
                        v-if="usuario.data.links.update"
                        class="flex flex-col justify-end space-y-3 space-x-0 md:flex-row md:space-y-0 md:space-x-3"
                    >
                        <ButtonText
                            v-if="!modoEdicao"
                            :texto="__('Editar')"
                            @click="modoEdicao = true"
                            dusk="editar"
                            icone="pencil-square"
                            type="button"
                        />

                        <ButtonText
                            v-if="modoEdicao"
                            :texto="__('Salvar')"
                            dusk="atualizar"
                            especie="acao"
                            icone="save"
                            type="submit"
                        />

                        <ButtonText
                            v-if="modoEdicao"
                            :texto="__('Cancelar')"
                            @click="cancelarEdicao"
                            dusk="cancelar"
                            especie="inacao"
                            icone="x-circle"
                            type="button"
                        />
                    </div>
                </div>
            </form>

            <Alerta v-show="!completo">
                <p>
                    {{
                        __(
                            'Campos que tornam o cadastro do usuário incompleto e, portanto, limitam o uso da aplicação:'
                        )
                    }}
                </p>

                <ul class="list-inside list-disc">
                    <li>{{ __('Nome') }}</li>
                    <li>{{ __('Matrícula') }}</li>
                    <li>{{ __('Usuário') }}</li>
                    <li>{{ __('Email') }}</li>
                    <li>{{ __('Lotação') }}</li>
                </ul>
            </Alerta>
        </Container>
    </Pagina>
</template>
