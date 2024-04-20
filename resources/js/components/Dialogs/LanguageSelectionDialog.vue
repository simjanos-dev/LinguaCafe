<template>
    <v-dialog content-class="language-selection-dialog" v-model="value" scrollable persistent>
        <v-card class="rounded-lg" :loading="loading">
            <v-card-title>
                <span class="text-h5">Language</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text>

                <template v-if="!everyLanguageInstalled">
                    installme
                </template>

                <!-- List of supported and installed languages -->
                <div id="language-buttons" class="d-flex flex-wrap mt-2" v-if="!loading">
                    <v-btn 
                        v-for="(language, index) in supportedLanguages"
                        rounded
                        depressed
                        :key="index"
                        class="language-button my-1 mx-1" 
                        @click="selectLanguage(language)" 
                    >
                        <v-img 
                            eager
                            class="border" 
                            :src="'/images/flags/' + language.toLowerCase() + '.png'" 
                            max-width="43" 
                            height="28"
                        ></v-img> 
                        <span>{{ language }}</span>
                    </v-btn>
                </div>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded text @click="close">Cancel</v-btn>
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
                loading: false,
                supportedLanguages: [],
                everyLanguageInstalled: true,
            };
        },
        watch: { 
            value: function(newVal, oldVal) {
                if (newVal) {
                    this.loadLanguages();
                }
            }
        },
        mounted: function() {
        },
        methods: {
            loadLanguages() {
                this.loading = true;
                this.everyLanguageInstalled = true;

                // get selected and supported languages
                axios.get('/languages/get-languages-for-language-selection-dialog').then((response) => {
                    this.supportedLanguages = response.data.languages;
                    this.everyLanguageInstalled = response.data.everyLanguageInstalled;
                    this.loading = false;
                });
            },
            selectLanguage(newLanguage) {
                var language = newLanguage.toLowerCase();
                axios.get('/languages/select/' + language).then(function (response) {
                    document.location.href = '/';
                }.bind(this)).catch(function (error) {}).then(() => {
                });
            },
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
