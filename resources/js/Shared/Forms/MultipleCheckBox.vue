<!--
    Múltiplos input do tipo checkbox.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
    @see https://kevinbigelow.medium.com/create-multiple-custom-checkboxes-bound-to-the-same-array-using-vue-3-and-the-composition-api-cbcdfe6216b7
 -->

<script setup>
import CheckBox from '@/Shared/Forms/CheckBox.vue';

const props = defineProps({
    disabled: { type: Boolean, default: false },
    value: { type: Array, required: true },
    opcoes: {
        type: Array,
        required: true,
        validator: (value) => {
            const hasNomeKey = value.every((option) => Object.keys(option).includes('nome'));
            const hasIdKey = value.every((option) => Object.keys(option).includes('id'));
            return hasNomeKey && hasIdKey;
        },
    },
});

const emit = defineEmits(['update:value']);

const check = (optionId, checked) => {
    if (props.disabled) {
        return;
    }

    // copy the value Array to avoid mutating props
    let updatedValue = [...props.value];
    // remove name if checked, else add name
    if (checked) {
        updatedValue.push(optionId);
    } else {
        updatedValue.splice(updatedValue.indexOf(optionId), 1);
    }

    emit('update:value', updatedValue);
};
</script>

<template>
    <div class="flex flex-col items-start space-y-3">
        <CheckBox
            v-for="opcao in opcoes"
            :id="opcao.nome"
            :key="opcao"
            :checked="value.includes(opcao.id)"
            :disabled="disabled"
            :label="opcao.nome"
            @update:checked="check(opcao.id, $event)"
        />
    </div>
</template>
