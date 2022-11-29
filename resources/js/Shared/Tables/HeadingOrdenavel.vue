<!--
    Heading (th) com função de ordenação.

    Notar que:
    - Notar que o componente depende do status do processamento geral da
    aplicação para definir seu próprio estado, por exemplo, caso a página
    esteja sendo processada, os botões são desabilitados;
    - Se o usuário clicar no botão de ordenação, um evento é emitido com o
    respectivo valor de ordenação (asc, desc ou undefined). Apenas para efeito
    estético, o ícone é alterado.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';
import { computed } from 'vue';

const props = defineProps({
    texto: { type: String, required: true },
    ordenacao: { type: String },
});

const emit = defineEmits(['ordenar']);

const status = useStatusRequisicaoStore();

const icone = computed(function () {
    if (props.ordenacao === 'asc') {
        return 'arrow-up-short';
    }
    if (props.ordenacao === 'desc') {
        return 'arrow-down-short';
    }
    return 'dash';
});

const setOrdenacao = () => {
    let ordenacao;

    if (props.ordenacao === 'asc') {
        ordenacao = 'desc';
    } else if (props.ordenacao === 'desc') {
        ordenacao = undefined;
    } else {
        ordenacao = 'asc';
    }

    emit('ordenar', ordenacao);
};
</script>

<template>
    <th class="p-3">
        <button
            :disabled="status.processando === true"
            @click="setOrdenacao"
            class="transform-gpu whitespace-nowrap rounded px-3 outline-none transition duration-300 hover:bg-primaria-300 focus:bg-primaria-300 disabled:cursor-not-allowed hover:dark:bg-secundaria-500 focus:dark:bg-secundaria-500"
            dusk="ordenacao"
        >
            <span class="font-bold">{{ texto }}</span>

            <Transition
                enter-from-class="rotate-180"
                enter-to-class="rotate-0"
                enter-active-class="transition duration-300 transform-gpu"
                leave-active-class="transition duration-300 transform-gpu"
                leave-from-class="rotate-0"
                leave-to-class="rotate-180"
            >
                <Icone :nome="icone" class="inline h-6 w-6" />
            </Transition>
        </button>
    </th>
</template>
