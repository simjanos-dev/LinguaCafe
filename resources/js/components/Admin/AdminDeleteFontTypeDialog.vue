<template>
    <v-dialog v-model="value" persistent max-width="500px" height="300px" scrollable>
        <v-card class="rounded-lg" :loading="deleting">
            <!-- Card title -->
            <v-card-title>
                <v-icon color="error" class="mr-2">mdi-delete</v-icon>Delete font

                <!-- Close button -->
                <v-spacer />
                    <v-btn icon @click="close" :disabled="deleting">
                        <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>

            <!-- Card content-->
            <v-card-text>
                Do you want to delete this font type?

                <!-- Error message -->
                <v-alert
                    v-if="!deleting && error"
                    class="rounded-lg mt-2 w-100"
                    color="error"
                    type="error"
                    border="left"
                    dark
                >
                    An error has occured while deleting the font type.
                </v-alert>
            </v-card-text>

            <!-- Card actions -->
            <v-card-actions class="flex-wrap">
                <v-spacer />
                
                <!-- Cancel button -->
                <v-btn 
                    rounded 
                    text 
                    :disabled="deleting"
                    @click="close" 
                >
                    Cancel
                </v-btn>

                <!-- Delete button -->
                <v-btn 
                    rounded 
                    depressed
                    color="error"
                    :disabled="deleting"
                    @click="deleteFont" 
                >
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
            id: Number
        },
        emits: ['input'],
        data: function() {
            return {
                deleting: false,
                error: false,
            };
        },
        mounted: function() {
        },
        methods: {
            deleteFont() {
                this.deleting = true;
                axios.post('/fonts/delete', {
                    id: this.$props.id,
                }).then(() => {
                    this.deleting = false;
                    this.$emit('fonts-changed');
                    this.close();
                }).catch(() => {
                    this.deleting = false;
                    this.error = true;
                });
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>

