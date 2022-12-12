<!--
    View home idealizada para usuários comuns, sem permissões administrativas
    e/ou gerenciais.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import InertiaLinkCard from '@/Shared/Inertia/InertiaLinkCard.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';

const props = defineProps({
    solicitacoes: { type: Object },
});

const __ = useTranslationsStore().__;
</script>

<template>
    <Pagina>
        <div
            class="flex justify-center space-x-6 bg-yellow-200 p-3 font-mono text-yellow-900 dark:bg-yellow-900 dark:text-yellow-50"
        >
            <span>
                {{ __('Processos solicitados:') }}

                <span
                    class="rounded-full bg-yellow-900 p-1 text-yellow-50 dark:bg-yellow-200 dark:text-yellow-900"
                >
                    {{ solicitacoes.data.solicitadas }}
                </span>
            </span>

            <span>
                {{ __('Processos entregues:') }}

                <span
                    class="rounded-full bg-yellow-900 p-1 text-yellow-50 dark:bg-yellow-200 dark:text-yellow-900"
                >
                    {{ solicitacoes.data.entregues }}
                </span>
            </span>
        </div>

        <Container>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <InertiaLinkCard
                    v-if="solicitacoes.links.create"
                    :href="solicitacoes.links.create"
                    :texto="__('Nova solicitação')"
                    icone="signpost"
                />

                <InertiaLinkCard
                    v-if="solicitacoes.links.view_any"
                    :href="solicitacoes.links.view_any"
                    :texto="__('Solicitações')"
                    icone="signpost-2"
                />
            </div>
        </Container>
    </Pagina>
</template>
