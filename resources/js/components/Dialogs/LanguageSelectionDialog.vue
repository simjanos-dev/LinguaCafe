<template>
    <v-dialog v-model="value" persistent max-width="300px">
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
                <div class="d-flex mt-4">
                    <b>Supported languages</b>
                    <v-spacer />
                    <v-menu offset-y left nudge-top="-12px">
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                        </template>
                        <v-card outlined class="rounded-lg pa-4" width="252px">
                            These languages should work properly, have dictionary sources provided and 
                            have "Simple" option for "Text processing method" when importing text.
                        </v-card>
                    </v-menu>
                </div>
                <v-list rounded>
                    <v-list-item-group color="primary" v-model="selectedLanguage">
                        <v-list-item 
                            v-for="(language, index) in supportedLanguages"
                            :key="index"
                            :value="language.toLowerCase()"
                            class="regular-list-height my-1" 
                            @click="selectLanguage(language)" 
                        >
                            <v-list-item-avatar tile min-width="60">
                                <v-img 
                                    class="border" 
                                    :src="'/images/flags/' + language.toLowerCase()" 
                                    max-width="43" 
                                    height="28"
                                ></v-img> 
                            </v-list-item-avatar>
                            <v-list-item-content>{{ language }}</v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>

                <!-- Experimental languages -->
                <div class="d-flex mt-4">
                    <b>Experimental languages</b>
                    <v-spacer />
                    <v-menu offset-y left nudge-top="-12px">
                        <template v-slot:activator="{ on, attrs }">
                            <v-icon v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                        </template>
                        <v-card outlined class="rounded-lg pa-4" width="252px">
                            These languages have been added recently and awaiting testing and 
                            community feedback to improve them. <br><br>
                            
                            They may have problems, not work properly or have no dictionary sources provided.
                        </v-card>
                    </v-menu>
                        
                </div>
                <v-list rounded>
                    <v-list-item-group color="primary" v-model="selectedLanguage">
                        <v-list-item 
                            v-for="(language, index) in experimentalLanguages"
                            :key="index"
                            :value="language.toLowerCase()"
                            class="regular-list-height my-1" 
                            @click="selectLanguage(language)" 
                        >
                            <v-list-item-avatar tile min-width="60">
                                <v-img 
                                    class="border" 
                                    :src="'/images/flags/' + language.toLowerCase()" 
                                    max-width="43" 
                                    height="28"
                                ></v-img> 
                            </v-list-item-avatar>
                            <v-list-item-content>{{ language }}</v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
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

                supportedLanguages: [
                    'German',
                    'Japanese',
                    'Norwegian',
                    'Spanish',
                ],
                experimentalLanguages: [
                    'Chinese',
                    'Dutch',
                    'Finnish',
                    'French',
                    'Italian',
                    'Korean',
                    'Russian',
                    'Swedish',
                    'Ukrainian',
                ]
            };
        },
        mounted: function() {
            axios.get('/language/get').then(function (response) {
                this.selectedLanguage = response.data;
            }.bind(this)).catch(function (error) {}).then(function () {});
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
