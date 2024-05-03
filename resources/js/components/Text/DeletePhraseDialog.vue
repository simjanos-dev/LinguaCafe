<template>
    <v-dialog v-model="value" persistent max-width="500px" height="300px">
        <v-card class="rounded-lg"   @mouseup.stop=";" @touchstart.stop=";" @touchend.stop=";">
            <v-card-title>
                <v-icon class="mr-2" color="error">mdi-alert-circle</v-icon>
                <span class="text-h5">Delete phrase</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-4 pb-6">
                Are you sure you want to delete the selected phrase?
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">
                    Cancel
                </v-btn>

                <v-btn rounded depressed color="error" @click="confirm">
                    <v-icon>mdi-delete</v-icon>
                    Delete
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
        },
        emits: ['input'],
        data: function() {
            return {
            };
        },
        mounted: function() {
        },
        methods: {
            confirm() {
                this.$emit('confirm');
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
