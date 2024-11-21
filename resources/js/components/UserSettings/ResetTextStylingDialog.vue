<template>
    <v-dialog v-model="value" persistent max-width="500px">
        <v-card class="rounded-lg">
            <v-card-title>
                <span class="text-h5">Reset text styling</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text>
                You can reset the text styling settings to default for your selected word status or for the entire selected theme.
                <div class="mt-2">The reset settings will not be saved automatically.</div>
                <v-radio-group
                    v-model="resetType"
                    class="mt-4"
                >
                    <v-radio
                        label="Selected word level"
                        value="level"
                    ></v-radio>
                    <v-radio
                        label="Selected theme"
                        value="theme"
                    ></v-radio>
                </v-radio-group>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>
                <v-btn rounded depressed color="primary" @click="reset">Reset</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean
        },
        emits: ['input', 'reset'],
        data: function() {
            return {
                resetType: 'level'
            };
        },
        mounted: function() {
        },
        methods: {
            reset() {
                this.$emit('reset', this.resetType)
                this.close()
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
