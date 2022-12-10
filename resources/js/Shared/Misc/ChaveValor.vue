<!--
    Exibe o par chave e valor estilizado como um 'input' desabilitado.

    Útil para exibir valores imutáveis, presentes na página para informação.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import MensagemErro from '@/Shared/Forms/MensagemErro.vue';
import Icone from '@/Shared/Icones/Icone.vue';
import Ancora from '@/Shared/Inertia/Ancora.vue';

defineProps({
    icone: { type: String },
    chave: { type: String, required: true },
    valor: { type: [Number, String] },
    erro: { type: String },
    href: { type: String },
});
</script>

<template>
    <div class="space-y-1">
        <div class="flex items-center space-x-1">
            <span class="font-bold">{{ chave }}</span>

            <Ancora v-if="href" :href="href" />
        </div>

        <p
            class="flex items-center space-x-4 rounded-lg border border-dashed border-primaria-500 bg-primaria-50 p-2 transition duration-300 dark:bg-secundaria-900 dark:text-primaria-50"
        >
            <Icone v-if="icone" :nome="icone" />

            <span v-if="valor">{{ valor }}</span>
        </p>

        <MensagemErro v-if="erro" :erro="erro" />
    </div>
</template>
