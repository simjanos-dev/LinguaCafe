<template>
    <v-dialog content-class="language-selection-dialog" v-model="value" scrollable persistent>
        <v-card class="rounded-lg">
            <v-card-title>
                <span class="text-h5">Language</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text>
                <!-- Supported languages -->
                You can find out more information about the supported languages in the 
                <a href="https://github.com/simjanos-dev/LinguaCafe#language-support"><v-icon class="mr-1">mdi-github</v-icon>GitHub</a> readme file.
                    <div id="language-buttons" class="d-flex flex-wrap mt-2">
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
                selectedLanguage: 'japanese',
                supportedLanguages: [],
            };
        },
        mounted: function() {
            // get selected language
            axios.get('/language/get').then((response) => {
                this.selectedLanguage = response.data;
            });

            // get supported language list
            axios.get('/config/get/linguacafe.languages.supported_languages').then((response) => {
                this.supportedLanguages = response.data;
            });

        },
        methods: {
            selectLanguage: function(newLanguage) {
                this.selectedLanguage = newLanguage.toLowerCase();
                axios.get('/language/change/' + this.selectedLanguage).then(function (response) {
                    document.location.href = '/';
                }.bind(this)).catch(function (error) {}).then(() => {
                });
            },
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
