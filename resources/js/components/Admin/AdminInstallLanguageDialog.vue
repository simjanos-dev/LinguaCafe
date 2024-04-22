<template>
    <v-dialog v-model="value" persistent max-width="500px" height="300px">
        <v-card class="rounded-lg" :loading="installing">
            <v-card-title>
                <v-icon class="mr-2">mdi-download</v-icon>
                <span class="text-h5">Install language</span>
                
                <v-spacer></v-spacer>
                <v-btn icon @click="close" :disabled="installing">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            
            <v-card-text class="pt-4 pb-6">
                <!-- Install confirmation message -->
                <template v-if="!installing && installResult !== 'success'">
                    Do you want to install {{ $props.language }} language? It will require internet connection, and can take several minutes.
                </template>

                <!-- Success message -->
                <v-alert
                    v-if="!installing && installResult === 'success'"
                    dense
                    class="rounded-lg"
                    color="success"
                    type="success"
                    border="left"
                >
                    {{ $props.language }} has been successfully installed.
                </v-alert>

                <!-- Error message -->
                <v-alert
                    v-if="!installing && installResult === 'error'"
                    dense
                    class="rounded-lg mt-4"
                    color="error"
                    type="error"
                    border="left"
                >
                    An error has occurred while installing the language.
                </v-alert>

                <!-- Installation in progress message -->
                <template v-if="installing">
                    {{ $props.language }} is being installed, it can take several minutes...
                </template>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                
                <!-- Cancel button -->
                <v-btn rounded text @click="close" :disabled="installing" v-if="installResult !== 'success'">
                    Cancel
                </v-btn>
                
                <!-- Close button -->
                <v-btn rounded text @click="close" :disabled="installing" v-if="installResult === 'success'">
                    Close
                </v-btn>
                
                <!-- Install button -->
                <v-btn rounded text @click="install" :disabled="installing" v-if="installResult !== 'success'">
                    <v-icon class="mr-1">mdi-download</v-icon>
                    Install
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {
        props: {
            value : Boolean,
            language: String,
        },
        emits: ['input'],
        data: function() {
            return {
                installResult: '',
                installing: false,
            };
        },
        mounted: function() {
        },
        methods: {
            install() {
                this.installing = true;
                axios.post('/languages/install', {
                    language: this.$props.language,
                }).then((response) => {
                    this.installing = false;
                    if (response.status === 200) {
                        this.installResult = 'success';
                        this.$emit('language-installed');
                    } else {
                        this.installResult = 'error';
                    }
                }).catch((error) => {
                    this.installing = false;
                    this.installResult = 'error';
                });
            },
            close() {
                this.installResult = '';
                this.installing = false;
                this.$emit('input', false);
            }
        }
    }
</script>
