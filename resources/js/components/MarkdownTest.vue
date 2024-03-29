<template>
        <vue-showdown
            v-if="markdownText.length"
            :markdown="markdownText"
            flavor="github"
            :options="{ ghCodeBlocks: true, ellipsis: true, emoji: true, tables: true}"
        ></vue-showdown>
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
            this.markdownText = response.data;
        });
    },
    methods: {
        
    }
}
</script>
