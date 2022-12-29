<!--
    View para executar a importação forçada de dados.

    Importação forçada ocorre por meio da requisição do usuário. É do tipo
    forçada, pois a aplicação possui rotinas diárias para executá-la
    automaticamente tornando desnecessário forçar a importação.

    Contudo, em certos cenários, ela é útil.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import MultipleCheckBox from '@/Shared/Forms/MultipleCheckBox.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';

const props = defineProps({
    opcoes: { type: Object },
    links: { type: Object },
});

const __ = useTranslationsStore().__;

const formImportacao = useForm({ importacoes: [] });

const importarDados = () => {
    formImportacao.post(props.links.store, {
        preserveScroll: true,

        onSuccess: () => flash(),
    });
};
</script>

<template>
    <Pagina :titulo="__('Importação forçada de dados')">
        <Container>
            <form @submit.prevent="importarDados" class="space-y-3">
                <fieldset class="rounded border border-primaria-500 p-3 dark:border-secundaria-100">
                    <legend class="px-3 font-mono">{{ __('Importar') }}</legend>

                    <MultipleCheckBox v-model:value="formImportacao.importacoes" :opcoes="opcoes" />
                </fieldset>

                <Transition
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    enter-active-class="transition duration-300 transform-gpu"
                    leave-active-class="transition duration-200 transform-gpu"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <ButtonText
                        v-show="formImportacao.importacoes.length >= 1"
                        :texto="__('Executar')"
                        dusk="submit"
                        especie="acao"
                        icone="play-circle"
                        type="submit"
                    />
                </Transition>
            </form>
        </Container>
    </Pagina>
</template>
