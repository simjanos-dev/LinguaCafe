<template>
    <v-dialog v-model="value" scrollable persistent max-width="820">
        <v-card 
            id="review-settings"
            outlined
            class="rounded-lg"
        >
            <v-card-title>
                <span class="text-h5">Settings</span>
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-6 pb-12" v-if="settingsLoaded">
                <!-- Text section-->
                <div class="subheader d-flex mb-2">
                    Text
                </div>

                <!-- Font size -->
                <v-row>
                    <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Font size:</v-col>
                    <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                        <v-slider
                            v-model="settings.fontSize"
                            :tick-labels="['Small', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Large']"
                            :tick-size="0"
                            :min="12"
                            :max="30"
                            step="1"
                            thumb-label="always"
                            thumb-size="38"
                            track-color="#c5c5c5"
                            @change="saveSettings"
                        ></v-slider>
                    </v-col>
                </v-row>

                <!-- Sentence mode -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Sentence mode:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-switch
                            color="primary"
                            v-model="settings.reviewSentenceMode"
                            @change="saveSettings"
                        ></v-switch>
                    </v-col>
                </v-row>

                <!-- Vocabulary hover box section-->
                <div class="subheader subheader-margin-top d-flex mb-2">
                    Vocabulary hover box
                </div>

                <!-- Vocabulary hover box -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Hover vocabulary box:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-menu offset-y left nudge-top="-12px">
                            <template v-slot:activator="{ on, attrs }">
                                <v-icon class="mr-2" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                            </template>
                            <v-card outlined class="rounded-lg pa-4" width="320px">
                                A minimalistic vocabulary box that appears when you move the mouse over a word or phrase.
                            </v-card>
                        </v-menu>

                        <v-switch
                            color="primary"
                            v-model="settings.vocabularyHoverBox"
                            @change="saveSettings"
                        ></v-switch>
                    </v-col>
                </v-row>

                <!-- Vocabulary hover box dictionary search -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Hover vocabulary dictionary search:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-switch
                            v-model="settings.vocabularyHoverBoxSearch"
                            color="primary"
                            @change="saveSettings"
                        ></v-switch>
                    </v-col>
                </v-row>

                <!-- Hover vocabulary delay -->
                <v-row>
                    <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Hover vocabulary delay:</v-col>
                    <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                        <v-slider
                            v-model="settings.vocabularyHoverBoxDelay"
                            :tick-labels="['200ms', '', '', '', '', '', '', '', '1000ms']"
                            :tick-size="0"
                            :min="200"
                            :max="1000"
                            thumb-label="always"
                            thumb-size="38"
                            step="100"
                            track-color="#c5c5c5"
                            @change="saveSettings"
                        >
                        </v-slider>
                    </v-col>
                </v-row>
            </v-card-text>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn rounded color="primary" @click="close">Close</v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
    export default {    
        emits: ['input'],   
        data: function() {
            return {
                settingsLoaded: false,
                cookieNames: {
                    fontSize: 'font-size',
                    vocabularyHoverBox: 'vocabulary-hover-box',
                    vocabularyHoverBoxSearch: 'vocabulary-hover-box-search',
                    vocabularyHoverBoxDelay: 'vocabulary-hover-delay',
                    reviewSentenceMode: 'review-sentence-mode',
                },
                settings: {},
            }
        },
        props: {
            value : Boolean,
        },
        mounted() {
            this.loadSetting('fontSize', 'integer', 20);
            this.loadSetting('vocabularyHoverBox', 'boolean', true);
            this.loadSetting('vocabularyHoverBoxSearch', 'boolean', true);
            this.loadSetting('vocabularyHoverBoxDelay', 'integer', 300);
            this.loadSetting('reviewSentenceMode', 'boolean', true);
            this.settingsLoaded = true;
            this.saveSettings();
        },
        methods: {
            saveSettings(settingName = '') {
                if (settingName == 'hideAllHighlights') {
                    this.settings.hideNewWordHighlights = this.settings.hideAllHighlights;
                }

                if (this.settings.fontSize < 12) {
                    this.settings.fontSize = 12;
                }

                if (this.settings.fontSize > 30) {
                    this.settings.fontSize = 30;
                }

                this.saveSetting('fontSize');
                this.saveSetting('vocabularyHoverBox');
                this.saveSetting('vocabularyHoverBoxSearch');
                this.saveSetting('vocabularyHoverBoxDelay');
                this.saveSetting('reviewSentenceMode');

                this.$emit('changed', this.settings);
            },
            saveSetting(name) {
                this.$cookie.set(this.cookieNames[name], this.settings[name], 3650);
            },
            changeSetting(name, value, emitResult = false) {
                this.settings[name] = value
                
                if (this.settings.fontSize < 12) {
                    this.settings.fontSize = 12;
                }

                if (this.settings.fontSize > 30) {
                    this.settings.fontSize = 30;
                }

                ;
                this.saveSetting(name);

                if (emitResult) {
                    this.$emit('changed', this.settings);
                }
            },
            loadSetting: function(name, type, defaultValue) {
                if (this.$cookie.get(this.cookieNames[name]) === null) {
                    this.settings[name] = defaultValue;
                } else {
                    if (type == 'boolean') {
                        this.settings[name] = this.$cookie.get(this.cookieNames[name]) === 'true';
                    }

                    if (type == 'integer') {
                        this.settings[name] = parseInt(this.$cookie.get(this.cookieNames[name]));
                    }

                    if (type == 'string') {
                        this.settings[name] = this.$cookie.get(this.cookieNames[name]);
                    }
                }

            },
            close(){
                this.$emit('input', false);
            }
        }
    }
</script>
