<!--
    View para listagem dos logs de funcionamento da aplicação.

    @see https://vuejs.org/guide/introduction.html
    @see https://tailwindcss.com/docs
    @see https://inertiajs.com/
    @see https://www.thisdot.co/blog/provide-inject-api-with-vue-3
 -->
<script setup>
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import InertiaButtonIconeLink from '@/Shared/Inertia/InertiaButtonIconeLink.vue';
import LinkButtonIcone from '@/Shared/Links/LinkButtonIcone.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';

const props = defineProps({
    arquivos: { type: Object },
});

const __ = useTranslationsStore().__;
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
</template>
