<!--
    View destinada a exibição de erros http.

    Esta view não usa o layout padrão da aplicação.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script>
export default {
    layout: null,
};
</script>

<script setup>
import LinkButtonText from '@/Shared/Links/LinkButtonText.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { computed } from 'vue';

const props = defineProps({
    status: { type: Number },
    link: { type: String },
});

const __ = useTranslationsStore().__;

const title = computed(() => {
    return {
        401: __('401: Acesso Não Autorizado'),
        403: __('403: Acesso Proibido'),
        404: __('404: Página Não Encontrada'),
        405: __('405: Método não Permitido'),
        419: __('419: Página Expirada'),
        429: __('429: Requisições em Excesso'),
        500: __('500: Erro Interno'),
        503: __('503: Serviço Indisponível'),
    }[props.status];
});

const description = computed(() => {
    return {
        401: __('Ooops!!! Suas credencias se perderam, tente se autenticar novamente.'),
        403: __('Ooops!!! Esse recurso não está disponível para você. Procure um administrador.'),
        404: __('Ooops!!! A página que você busca não existe. Verifique a URL digitada.'),
        405: __('Ooops!!! Esse método não é permitido para essa URL.'),
        419: __('Ooops!!! A sua requisição expirou, tente se autenticar novamente.'),
        429: __('Ooops!!! Você fez mais requisições por segundo que o permitido pela aplicação.'),
        500: __(
            'Ooops!!! Salve-se quem puder, pois o servidor está com problemas graves. Procure um administrador.'
        ),
        503: __(
            'Ooops!!! Os serviços estão indisponíveis para manutenção. Tente novamente mais tarde.'
        ),
    }[props.status];
});
</script>

<template>
    <div
        class="flex min-h-screen transform-gpu flex-col items-center justify-center space-y-12 bg-primaria-50 text-xl text-primaria-900 transition duration-300 dark:bg-secundaria-900 dark:text-secundaria-50"
    >
        <h1 class="rounded-full bg-primaria-500 p-10 text-4xl font-extrabold text-primaria-50">
            {{ title }}
        </h1>

        <p class="text-2xl">{{ description }}</p>

        <LinkButtonText :href="link" :texto="__('Voltar')" especie="padrao" />
    </div>
</template>
