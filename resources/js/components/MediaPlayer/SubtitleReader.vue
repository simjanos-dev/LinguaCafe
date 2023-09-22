<template>
    <v-container class="vocab-box-area">
        <text-block-group
        v-if="textBlocks.length"
        ref="textBlock"
        :theme="'light'"
        :fullscreen="false"
        :_text-blocks="textBlocks"
        :language="'japanese'"
        :highlight-words="true"
        :plain-text-mode="false"
        :font-size="20"
        :line-spacing="1"
    ></text-block-group>    
    </v-container>
</template>


<script>
const moment = require('moment');
export default {
    data: function () {
        return {
        }
    },
    props: {
        textBlocks: {
            type: Array,
            default: []
        }
    },
    mounted() {
    },
    methods: {
    }
}
</script>