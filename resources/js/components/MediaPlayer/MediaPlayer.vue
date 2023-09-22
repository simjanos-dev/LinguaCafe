<template>
    <v-container class="d-flex flex-column justify-center flex-nowrap">
        <subtitle-list
            @subtitle-change="subtitleChange"
        ></subtitle-list>
        <subtitle-reader
            :text-blocks="textBlocks"
        ></subtitle-reader>
    </v-container>
</template>


<script>
const moment = require('moment');
export default {
    data: function () {
        return {
            textBlocks: [],
        }
    },
    props: {

    },
    mounted() {
        
    },
    methods: {
        subtitleChange: function(selectedSubtitle) {
            console.log(selectedSubtitle);
            this.textBlocks = [];
            axios.post('/jellyfin/process-subtitles', selectedSubtitle).then((result) => {
                console.log(result.data);
                this.textBlocks = result.data;
            });
        }
    }
}
</script>