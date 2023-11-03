<template>
    <v-container id="home" class="pb-12">
        <calendar></calendar>
        <goals></goals>
        <statistics></statistics>
    </v-container>
</template>


<script>
    import {formatNumber} from './../../helper.js';
    const moment = require('moment');
    export default {
        data: function() {
            return {
                theme: (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme'),
            }
        },
        props: {
            
        },
        mounted() {
        },
        methods: {
            formatNumber: formatNumber,
        }
    }
</script>
