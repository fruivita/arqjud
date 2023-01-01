<!--
    View para visualização do log de funcionamento da aplicação.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { useExclusao } from '@/Composables/UseExclusao';
import { perPageKey, updatePerPageKey } from '@/keys';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import LinkButtonText from '@/Shared/Links/LinkButtonText.vue';
import ModalConfirmacao from '@/Shared/Modals/ModalConfirmacao.vue';
import Paginacao from '@/Shared/Tables/Paginacao.vue';
import PorPagina from '@/Shared/Tables/PorPagina.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { Inertia } from '@inertiajs/inertia';
import { provide, readonly, ref, watch } from 'vue';

const props = defineProps({
    conteudo: { type: Object },
});

const __ = useTranslationsStore().__;

const { confirmarExclusao, excluir, titulo } = useExclusao();

const perPage = ref(props.conteudo.meta.per_page);
const updatePerPage = (novoValor) => {
    perPage.value = novoValor;
};
provide(perPageKey, readonly(perPage));
provide(updatePerPageKey, updatePerPage);

const filtrar = () => {
    Inertia.get(
        props.conteudo.meta.path,
        { per_page: perPage.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['conteudo'],
        }
    );
};

watch(perPage, filtrar);
</script>

<template>
    <Pagina :titulo="conteudo.meta.arquivo">
        <Container class="space-y-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-end">
                <LinkButtonText
                    :href="conteudo.meta.links.download"
                    :texto="__('Download')"
                    especie="acao"
                    icone="download"
                />

                <ButtonText
                    v-if="conteudo.meta.links.delete"
                    @click="
                        confirmarExclusao(
                            conteudo.meta.links.delete,
                            __('Exclusão do log :attribute', {
                                attribute: conteudo.meta.arquivo,
                            })
                        )
                    "
                    :texto="__('Excluir')"
                    especie="perigo"
                    icone="trash"
                />

                <PorPagina />
            </div>

            <template v-if="conteudo.data.length">
                <div class="space-y-3">
                    <p v-for="(registro, indice) in conteudo.data" :key="indice">
                        {{ registro.linha }}
                    </p>
                </div>
            </template>

            <template v-else>
                <p>{{ __('Sem conteúdo!') }}</p>
            </template>

            <Paginacao v-if="conteudo.meta.last_page > 1" :meta="conteudo.meta" />
        </Container>
    </Pagina>

    <Teleport to="body">
        <ModalConfirmacao>
            <template #header>
                <span>{{ titulo() }}</span>
            </template>

            <template #footer>
                <ButtonText
                    :texto="__('Confirmar')"
                    @click="excluir"
                    especie="perigo"
                    icone="check-circle"
                />
            </template>
        </ModalConfirmacao>
    </Teleport>
</template>
