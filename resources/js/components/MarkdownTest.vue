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
            dom = dom.replaceAll('[!NOTE]', '<note><i aria-hidden="true" class="v-icon notranslate mdi mdi-alert-circle-outline"></i> <span>Note</span></note>');

            return dom;
        }
    }
}
</script>
