<template>
    <v-card 
        outlined 
        id="vocab-hover-box" 
        class="d-flex flex-wrap rounded-lg pa-2"
        :style="{
            'position': 'absolute',
            'left': $props.positionLeft + 'px',
            'top': $props.positionTop + 'px',
        }"
    >
        <div class="reading" v-if="$props.reading.length">
            {{ $props.reading }}
        </div>

        <ul class="mb-0" v-if="$props.translation.length">
            <li v-for="(translation, translationIndex) in translationList" :key="translationIndex">
                {{ translation }}
            </li>
        </ul>
    </v-card>
</template>

<script>
    export default {
        data: function() {
            return {
                translationList: []
            }
        },
        props: {
            translation: String,
            positionLeft: Number,
            positionTop: Number,
            reading: String
        },
        mounted() {
            this.translationList = this.$props.translation.split(';');

        },
        methods: {
        }
    }
</script>
