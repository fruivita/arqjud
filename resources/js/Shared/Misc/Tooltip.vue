<!--
    Exibe um tooltip ao se clicar no botão.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import Icone from '@/Shared/Icones/Icone.vue';
import { arrow, computePosition, flip, offset, shift } from '@floating-ui/dom';
import { onClickOutside } from '@vueuse/core';
import { isArray } from 'lodash';
import { ref } from 'vue';

const props = defineProps({
    icone: { type: String, default: 'info-circle' },
    texto: { type: [Array, String], required: true },
    posicao: {
        type: String,
        default: 'bottom',
        validator(value) {
            return ['bottom', 'top', 'left', 'right'].includes(value);
        },
    },
});

const referenceRef = ref(null);
const floatingRef = ref(null);
const arrowRef = ref(null);
const exibirTooltip = ref(false);
const clicked = ref(false);

const click = () => {
    exibirTooltip.value = true;
    clicked.value = true;
    calcularPosicao();
};

const mouveover = () => {
    if (exibirTooltip.value !== true) {
        exibirTooltip.value = true;
        calcularPosicao();
    }
};

const mouseout = () => {
    if (clicked.value !== true) {
        exibirTooltip.value = false;
    }
};

const calcularPosicao = async () => {
    const { x, y, middlewareData, placement } = await computePosition(
        referenceRef.value,
        floatingRef.value,
        {
            placement: props.posicao.value,
            middleware: [offset(8), flip(), shift(), arrow({ element: arrowRef.value })],
        }
    );

    Object.assign(floatingRef.value.style, {
        left: `${x}px`,
        top: `${y}px`,
    });

    const { x: arrowX, y: arrowY } = middlewareData.arrow;

    const ladoOposto = {
        left: 'right',
        right: 'left',
        bottom: 'top',
        top: 'bottom',
    }[placement.split('-')[0]];

    Object.assign(arrowRef.value.style, {
        left: arrowX ? `${arrowX}px` : '',
        top: arrowY ? `${arrowY}px` : '',
        bottom: '',
        right: '',
        [ladoOposto]: '-4px',
    });
};

onClickOutside(floatingRef, () => {
    exibirTooltip.value = false;
    clicked.value = false;
});
</script>

<template>
    <div class="inline-block">
        <button
            ref="referenceRef"
            @click="click"
            @mouseover="mouveover"
            @mouseout="mouseout"
            class="rounded p-1 outline-none ring-primaria-500 hover:ring-2 focus:ring-2 dark:ring-secundaria-500"
            dusk="toggle"
            type="button"
        >
            <Icone :nome="icone" class="h-4 w-4" />
        </button>

        <div
            v-show="exibirTooltip"
            ref="floatingRef"
            class="absolute top-0 left-0 cursor-default rounded-md bg-primaria-600 px-3 py-1.5 text-sm font-bold text-primaria-50 dark:bg-secundaria-600 dark:text-secundaria-50"
            dusk="tooltip"
        >
            <template v-if="isArray(texto)">
                <span v-for="(txt, indice) in texto" :key="indice" class="block">{{ txt }}</span>
            </template>

            <template v-else>
                <span>{{ texto }}</span>
            </template>

            <div
                ref="arrowRef"
                class="absolute h-[8px] w-[8px] rotate-45 bg-primaria-600 dark:bg-secundaria-600"
            ></div>
        </div>
    </div>
</template>
