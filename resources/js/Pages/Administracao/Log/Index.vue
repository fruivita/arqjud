<!--
    View para listagem dos logs de funcionamento da aplicação.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
    @see https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->
<script setup>
import { useExclusao } from '@/Composables/UseExclusao';
import ButtonIcone from '@/Shared/Buttons/ButtonIcone.vue';
import ButtonText from '@/Shared/Buttons/ButtonText.vue';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
import LinkButtonIcone from '@/Shared/Links/LinkButtonIcone.vue';
import ModalConfirmacao from '@/Shared/Modals/ModalConfirmacao.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';

const props = defineProps({
    arquivos: { type: Object },
});

const __ = useTranslationsStore().__;

const { confirmarExclusao, excluir, titulo } = useExclusao();
</script>

<template>
    <Pagina :titulo="__('Logs de funcionamento')">
        <Container>
            <Tabela>
                <template #header>
                    <Heading :texto="__('Logs')" />

                    <Heading :texto="__('Ações')" />
                </template>

                <template #body>
                    <template v-if="arquivos.data.length">
                        <Row v-for="(arquivo, index) in arquivos.data" :key="index">
                            <Cell>{{ arquivo.nome }}</Cell>

                            <Cell class="w-10">
                                <div class="flex space-x-3">
                                    <InertiaButtonIconeLink
                                        v-if="arquivo.links.view"
                                        :href="arquivo.links.view"
                                        icone="eye"
                                    />

                                    <LinkButtonIcone
                                        v-if="arquivo.links.download"
                                        :href="arquivo.links.download"
                                        icone="download"
                                    />

                                    <ButtonIcone
                                        v-if="arquivo.links.delete"
                                        @click="
                                            confirmarExclusao(
                                                arquivo.links.delete,
                                                __('Exclusão do log :attribute', {
                                                    attribute: arquivo.nome,
                                                })
                                            )
                                        "
                                        especie="perigo"
                                        icone="trash"
                                    />
                                </div>
                            </Cell>
                        </Row>
                    </template>

                    <template v-else>
                        <Row>
                            <Cell colspan="2">{{ __('Nenhum registro encontrado!') }}</Cell>
                        </Row>
                    </template>
                </template>
            </Tabela>
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
