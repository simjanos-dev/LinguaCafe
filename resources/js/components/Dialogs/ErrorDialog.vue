<template>
    <v-dialog v-model="value" persistent max-width="500px" height="300px">
        <v-card class="rounded-lg">
            <v-card-title>
                <v-icon large class="mr-2" color="error">mdi-alert-circle</v-icon>
                <span class="text-h5">{{ $props.title }}</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-4 pb-6">
                {{ $props.content }}
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">{{ $props.buttonText }}</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
            title: {
                type: String,
                default: 'Error'
            },
            content: {
                type: String,
                default: 'Error'
            },
            buttonText: {
                type: String,
                default: 'Close'
            },
        },
        emits: ['input'],
        data: function() {
            return {
            };
        },
        mounted: function() {
        },
        methods: {

            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
