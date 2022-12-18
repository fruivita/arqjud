<!--
    View para visualização da guia.

    @link https://vuejs.org/guide/introduction.html
    @link https://tailwindcss.com/docs
    @link https://inertiajs.com/
 -->

<script setup>
import { mascaraCNJ } from '@/keys.js';
import Lotacao from '@/Models/Lotacao.js';
import Container from '@/Shared/Containers/Container.vue';
import Pagina from '@/Shared/Containers/Pagina.vue';
import LinkButtonText from '@/Shared/Links/LinkButtonText.vue';
import ChaveValor from '@/Shared/Misc/ChaveValor.vue';
import Clipboard from '@/Shared/Misc/Clipboard.vue';
import Cell from '@/Shared/Tables/Cell.vue';
import Heading from '@/Shared/Tables/Heading.vue';
import Row from '@/Shared/Tables/Row.vue';
import Tabela from '@/Shared/Tables/Tabela.vue';
import { useTranslationsStore } from '@/Stores/TranslationsStore';
import { mask } from 'maska';

const props = defineProps({
    guia: { type: Object },
});

const __ = useTranslationsStore().__;
</script>

<template>
    <Pagina :titulo="__(':attribute: Modo Visualização', { attribute: 'Guia' })">
        <Container>
            <div class="space-y-6">
                <div class="grid grid-cols-1 gap-x-3 gap-y-6 xl:grid-cols-2">
                    <ChaveValor
                        :chave="__('Número')"
                        :valor="`${guia.data.numero}/${guia.data.ano}`"
                        icone="tag"
                    />

                    <ChaveValor
                        :chave="__('Gerada em')"
                        :valor="guia.data.gerada_em"
                        icone="calendar-event"
                    />

                    <ChaveValor
                        :chave="__('Remetente')"
                        :valor="guia.data.remetente.nome ?? guia.data.remetente.username"
                        icone="person"
                    />

                    <ChaveValor
                        :chave="__('Recebedor')"
                        :valor="guia.data.recebedor.nome ?? guia.data.recebedor.username"
                        icone="person"
                    />

                    <ChaveValor
                        :chave="__('Lotação destinatária')"
                        :valor="new Lotacao(guia.data.lotacao_destinataria).nomeExibicao()"
                        icone="building"
                    />
                </div>

                <Tabela>
                    <template #header>
                        <Heading :texto="__('Processos')" />

                        <Heading :texto="__('Volumes')" />
                    </template>

                    <template #body>
                        <template v-if="guia.data.processos.length">
                            <Row v-for="(processo, indice) in guia.data.processos" :key="indice">
                                <Cell>
                                    <Clipboard :copiavel="mask(processo.numero, mascaraCNJ)" />
                                </Cell>

                                <Cell>{{ processo.qtd_volumes }}</Cell>
                            </Row>
                        </template>

                        <template v-else>
                            <Row>
                                <Cell colspan="2">{{ __('Nenhum registro encontrado!') }}</Cell>
                            </Row>
                        </template>
                    </template>
                </Tabela>

                <LinkButtonText
                    :href="guia.data.links.pdf"
                    :texto="__('Imprimir')"
                    especie="acao"
                    icone="printer"
                    target="_blank"
                />
            </div>
        </Container>
    </Pagina>
</template>
