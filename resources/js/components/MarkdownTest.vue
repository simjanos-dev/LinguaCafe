<template>
    <v-container id="user-manual">
        <vue-showdown
            v-if="markdownText.length"
            :markdown="markdownText"
            flavor="github"
            :options="{ ghCodeBlocks: true, ellipsis: true, emoji: true, tables: true}"
        ></vue-showdown>
    </v-container>
</template>

<script>
export default {
    props: {
    },
    data: function() {
        return {
            markdownText: '',
        };
    },
    mounted: function() {
        axios.get('/markdown-test.md').then((response) => {
            this.markdownText = this.replaceElements(response.data);
        });
    },
    methods: {
        replaceElements(dom) {
            dom = dom.replaceAll('[!NOTE]', '<admonition class="note"><i aria-hidden="true" class="v-icon notranslate mdi mdi-information-outline"></i> <span>Note</span></admonition>');
            dom = dom.replaceAll('[!TIP]', '<admonition class="tip"><i aria-hidden="true" class="v-icon notranslate mdi mdi-lightbulb-outline"></i> <span>Tip</span></admonition>');
            dom = dom.replaceAll('[!IMPORTANT]', '<admonition class="important-note"><i aria-hidden="true" class="v-icon notranslate mdi mdi-message-alert-outline"></i> <span>Important</span></admonition>');
            dom = dom.replaceAll('[!WARNING]', '<admonition class="warning"><i aria-hidden="true" class="v-icon notranslate mdi mdi-alert-outline"></i> <span>Warning</span></admonition>');
            dom = dom.replaceAll('[!CAUTION]', '<admonition class="caution"><i aria-hidden="true" class="v-icon notranslate mdi mdi-alert-circle-outline"></i> <span>Caution</span></admonition>');

            return dom;
        }
    }
}
</script>
