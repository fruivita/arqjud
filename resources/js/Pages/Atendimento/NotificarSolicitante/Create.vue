<!--
    View para a notificação individual do solicitante da disponibilidade de
    processo solicitado disponível para retirada.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { flash } from '@/Composables/UseFlash';
import { maxViewItems } from '@/keys';
import { mascaraCNJ } from '@/keys.js';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useStatusRequisicaoStore } from '@/Stores/StatusRequisicaoStore';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { useForm } from '@inertiajs/inertia-vue3';
import { slice } from 'lodash';
import { computed, ref } from 'vue';

const props = defineProps({
    links: { type: Object },
});

const __ = useTranslationsStore().__;

const status = useStatusRequisicaoStore();

const formNotificarSolicitante = useForm({ numero: '' });

// todos os processos cujo solicitante foi notificado na corrente operação
const processosNotificados = ref([]);

// exibe todos os processos ou apenas alguns (últimos X adicionados)
const exibirTodos = ref(false);

// processos em exibição na view de acordo com o valor de exibirTodos
const processos = computed(() =>
    exibirTodos.value
        ? processosNotificados.value
        : slice(processosNotificados.value, 0, maxViewItems)
);

const notificarSolicitante = async () => {
    if (status.processando == true) {
        flash({ alerta: __('Aguarde a conclusão da solicitação') });

        return;
    }

    if (formNotificarSolicitante.numero.length != 25) {
        formNotificarSolicitante.setError(
            'numero',
            __('Informe o número completo do processo no padrão CNJ')
        );

        return;
    }

    status.setStatus(true);
    formNotificarSolicitante.clearErrors();

    formNotificarSolicitante.post(props.links.notificar, {
        preserveScroll: true,

        onSuccess: () => {
            flash();
            processosNotificados.value.unshift(formNotificarSolicitante.numero);
            formNotificarSolicitante.reset();
        },
    });
};
</script>

<template>
    <Pagina :titulo="__('Notificar solicitante de processos')">
        <Container>
            <form @submit.prevent="notificarSolicitante" class="flex space-x-3">
                <div class="w-full">
                    <TextInput
                        v-model="formNotificarSolicitante.numero"
                        :erro="formNotificarSolicitante.errors.numero"
                        :label="__('Processo')"
                        :mascara="mascaraCNJ"
                        :maxlength="25"
                        :placeholder="__('Apenas números')"
                        autocomplete="off"
                        icone="journal-bookmark"
                        required
                    />
                </div>

                <ButtonIcone dusk="submit" especie="acao" icone="plus-circle" type="submit" />
            </form>
        </Container>

        <Transition
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            enter-active-class="transition duration-300 transform-gpu"
            leave-active-class="transition duration-200 transform-gpu"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <Container v-show="processosNotificados.length >= 1">
                <Tabela>
                    <template #header>
                        <Heading
                            :texto="
                                __('Solicitantes notificados: :attribute', {
                                    attribute: processosNotificados.length,
                                })
                            "
                        />
                    </template>

                    <template #body>
                        <template v-if="processos.length >= 1">
                            <Row v-for="processo in processos" :key="processo">
                                <Cell>{{ processo }}</Cell>
                            </Row>
                        </template>

                        <template v-else>
                            <Row>
                                <Cell colspan="1">{{ __('Nenhuma notificação!') }}</Cell>
                            </Row>
                        </template>
                    </template>
                </Tabela>
            </Container>
        </Transition>
    </Pagina>
</template>
