<!--
    Exibe informação sobre determinado elemento no mouseover e mouseout.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { arrow, computePosition, flip, offset, shift } from '@floating-ui/dom';
import { onClickOutside } from '@vueuse/core';
import { isArray } from 'lodash';
import { ref } from 'vue';

const props = defineProps({
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
const exibirHint = ref(false);

const mouveover = () => {
    if (exibirHint.value !== true) {
        exibirHint.value = true;
        calcularPosicao();
    }
};

const mouseout = () => {
    exibirHint.value = false;
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
    exibirHint.value = false;
});
</script>

<template>
    <div class="inline-block">
        <div
            ref="referenceRef"
            @mouseout="mouseout"
            @mouseover="mouveover"
            class="flex"
            dusk="toggle"
        >
            <slot></slot>
        </div>

        <div
            v-show="exibirHint"
            ref="floatingRef"
            class="absolute top-0 left-0 z-10 cursor-default rounded-md bg-primaria-600 px-3 py-1.5 text-sm font-bold text-primaria-50 dark:bg-secundaria-600 dark:text-secundaria-50"
            dusk="hint"
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
