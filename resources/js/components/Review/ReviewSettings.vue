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

                <!-- Font type -->
                <v-row v-if="fontTypes.length">
                    <v-col cols="12" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Font type:</v-col>
                    <v-col cols="12" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-select
                            v-model="selectedFontType"
                            :items="fontTypes"
                            item-text="name"
                            item-value="id"
                            dense
                            rounded
                            filled
                            hide-details
                            @change="saveSettings"
                        ></v-select>
                    </v-col>
                </v-row>

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
                    <v-col cols="4" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Sentence mode:</v-col>
                    <v-col cols="8" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-select
                            v-model="settings.reviewSentenceMode"
                            :items="sentenceModes"
                            item-text="name"
                            item-value="value"
                            dense
                            rounded
                            filled
                            hide-details
                            @change="saveSettings"
                        ></v-select>
                    </v-col>
                </v-row>

                <!-- Vocabulary box section-->
                <div class="subheader subheader-margin-top d-flex mb-2">
                    Vocabulary box
                </div>
                
                <!-- Vocabulary bottom sheet -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5 ">
                        Vocabulary bottom sheet:
                    </v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <!-- Vocabulary sidebar info box -->
                        <v-menu offset-y left nudge-top="-12px">
                            <template v-slot:activator="{ on, attrs }">
                                <v-icon class="mr-2" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                            </template>
                            <v-card outlined class="rounded-lg pa-4" width="320px">
                                A bottom sheet vocabulary designed for mobile screens, that replaces the popup vocabulary. <br><br>
                                This option is only available for devices with less than or equal to 768px screen width. 
                            </v-card>
                        </v-menu>

                        <v-switch
                            color="primary"
                            v-model="settings.vocabularyBottomSheet" 
                            @change="saveSettings"
                        ></v-switch>
                    </v-col>
                </v-row>

                <!-- Vocabulary hover box section-->
                <div class="subheader subheader-margin-top d-flex mb-2">
                    Vocabulary hover box
                </div>

                <!-- Vocabulary hover box -->
                <v-alert
                    v-if="settings.reviewSentenceMode !== 'interactive-text'"
                    type="error"
                    color="warning"
                >
                    Hover vocabulary box only works if you set the "Sentence mode" option to "Interactive text".
                </v-alert>

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

                <!-- Hover vocabulary preferred position -->
                <v-row>
                    <v-col cols="12" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Preferred position:</v-col>
                    <v-col cols="12" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-select
                            v-model="settings.vocabularyHoverBoxPreferredPosition"
                            :items="vocabularyHoverBoxPreferredPositionData"
                            item-text="name"
                            item-value="value"
                            dense
                            rounded
                            filled
                            hide-details
                            @change="saveSettings"
                        ></v-select>
                    </v-col>
                </v-row>

                <!-- Text to speech section -->
                <div class="subheader subheader-margin-top d-flex mb-2">
                    Text to speech
                </div>
                
                <!-- Text to speech -->
                <v-row v-if="textToSpeechVoices.length">
                    <v-col cols="12" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">TTS voice:</v-col>
                    <v-col cols="12" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-select
                            v-model="textTospeechSelectedVoice"
                            :items="textToSpeechVoices"
                            item-text="name"
                            item-value="name"
                            dense
                            rounded
                            filled
                            hide-details
                            @change="saveSettings"
                        ></v-select>
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
    import TextToSpeechService from './../../services/TextToSpeechService';
    import FontTypeService from './../../services/FontTypeService';
    export default {    
        emits: ['input'],   
        data: function() {
            return {
                /*
                    Text to speech and font type settings are handled differently, 
                    because they are a separate setting for every language.
                */

                fontTypeService: new FontTypeService(this.$props.language, this.$cookie, this.fontTypesLoaded),
                fontTypes: [],
                selectedFontType: null,
                textToSpeechService: new TextToSpeechService(this.$props.language, this.$cookie, this.textToSpeechVoicesChanged),
                textToSpeechVoices: [],
                textTospeechSelectedVoice: null,

                settingsLoaded: false,
                cookieNames: {
                    fontSize: 'font-size',
                    vocabularyHoverBox: 'vocabulary-hover-box',
                    vocabularyHoverBoxSearch: 'vocabulary-hover-box-search',
                    vocabularyHoverBoxDelay: 'vocabulary-hover-delay',
                    vocabularyHoverBoxPreferredPosition: 'vocabulary-hover-box-preferred-position',
                    vocabularyBottomSheet: 'vocabulary-bottom-sheet',
                    reviewSentenceMode: 'review-sentence-mode',
                },
                sentenceModes: [
                    {
                        name: 'Disabled',
                        value: 'disabled',
                    },
                    {
                        name: 'Plain text',
                        value: 'plain-text',
                    },
                    {
                        name: 'Interactive text',
                        value: 'interactive-text',
                    },
                ],
                settings: {},
                vocabularyHoverBoxPreferredPositionData: [
                    {
                        name: 'Below the hovered word',
                        value: 'bottom'
                    },
                    {
                        name: 'Above the hovered word',
                        value: 'top'
                    },
                ],
            }
        },
        props: {
            value : Boolean,
            language: String,
        },
        mounted() {
            this.loadSetting('fontSize', 'integer', 20);
            this.loadSetting('vocabularyHoverBox', 'boolean', true);
            this.loadSetting('vocabularyHoverBoxSearch', 'boolean', true);
            this.loadSetting('vocabularyHoverBoxDelay', 'integer', 300);
            this.loadSetting('vocabularyHoverBoxPreferredPosition', 'string', 'bottom');
            this.loadSetting('vocabularyBottomSheet', 'boolean', true);
            this.loadSetting('reviewSentenceMode', 'string', 'plain-text');
            
            this.settingsLoaded = true;
            this.saveSettings();
        },
        methods: {
            fontTypesLoaded() {
                // set selected font
                this.selectedFontType = this.fontTypeService.getSelectedFontTypeId();

                // set font list
                this.fontTypes = this.fontTypeService.fonts;
            },
            textToSpeechVoicesChanged() {
                // set selected voice
                var selectedVoice = this.textToSpeechService.getSelectedVoice();
                if (selectedVoice !== null) {
                    this.textTospeechSelectedVoice = selectedVoice.name;
                }

                // get list of voice
                this.textToSpeechVoices = this.textToSpeechService.getVoiceNames();
            },
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
                this.saveSetting('vocabularyHoverBoxPreferredPosition');
                this.saveSetting('vocabularyBottomSheet');
                this.saveSetting('reviewSentenceMode');

                // save text to speech
                if (this.textTospeechSelectedVoice !== null) {
                    this.$cookie.set(this.$props.language + '-text-to-speech-voice', this.textTospeechSelectedVoice, 3650);
                }

                // save font
                if (this.fontTypeService !== null && this.selectedFontType) {
                    this.fontTypeService.selectFontType(this.selectedFontType);
                    this.fontTypeService.loadSelectedFontTypeIntoDom(this.selectedFontType);
                }

                this.$emit('changed', this.settings);
                
                this.$forceUpdate();
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
