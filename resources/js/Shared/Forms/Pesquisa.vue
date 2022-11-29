<!--
    Componente de pesquisa.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script>
export default { inheritAttrs: false };
</script>

<script setup>
import ContadorCaracteres from '@/Shared/Forms/ContadorCaracteres.vue';
import MensagemErro from '@/Shared/Forms/MensagemErro.vue';
import Icone from '@/Shared/Icones/Icone.vue';
import { usePage } from '@inertiajs/inertia-vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    modelValue: { type: String },
    maxlength: { type: [Number, String], default: 50 },
});

const exibirContador = ref(false);

const erro = computed(() => usePage().props.value.errors.termo);

const emit = defineEmits(['update:modelValue']);

const onInput = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <div class="space-y-1 md:mx-auto md:w-2/4">
        <div
            :class="{
                'text-primaria-900 dark:text-secundaria-50': !erro,
                'text-red-900': erro,
            }"
            class="relative text-primaria-900"
        >
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <Icone nome="search" />
            </div>

            <input
                :class="{
                    'border-primaria-500 bg-primaria-100 ring-primaria-500 dark:border-secundaria-600 dark:bg-secundaria-800 dark:ring-secundaria-500':
                        !erro,
                    'border-red-500 bg-red-100 ring-red-500': erro,
                }"
                :maxlength="maxlength"
                :value="modelValue"
                @blur="exibirContador = false"
                @focus="exibirContador = true"
                @input="onInput"
                v-bind="$attrs"
                class="w-full rounded-lg border py-2 pl-12 pr-20 outline-none focus:ring"
                autocomplete="off"
                type="text"
            />

            <ContadorCaracteres
                v-show="exibirContador"
                :maxlength="maxlength"
                :texto="modelValue"
            />
        </div>

        <MensagemErro v-if="erro" :erro="erro" />
    </div>
</template>
