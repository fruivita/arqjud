<!--
    Input to tipo checkbox com texto clicável.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
 -->

<script>
export default { inheritAttrs: false };
</script>

<script setup>
import { gerarID } from '@/Composables/UseGerarID';
import { computed } from 'vue';

const props = defineProps({
    id: { type: String },
    label: { type: String, required: true },
    disabled: { type: Boolean, default: false },
    modelValue: { type: Boolean, required: true },
});

const emit = defineEmits(['update:modelValue']);

const idReal = computed(() => props.id ?? gerarID());

const onClick = (event) => {
    if (!props.disabled) {
        emit('update:modelValue', event.target.checked);
    }
};
</script>

<template>
    <label :for="idReal" class="inline-flex select-none items-center space-x-2" dusk="label">
        <input
            :id="idReal"
            :checked="modelValue"
            :disabled="disabled"
            @click="onClick"
            v-bind="$attrs"
            class="h-5 w-5 accent-primaria-500"
            type="checkbox"
        />

        <span>{{ label }}</span>
    </label>
</template>
