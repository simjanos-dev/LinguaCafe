<template>
    <v-dialog v-model="value" persistent scrollable max-width="1000">
        <v-card 
            id="text-reader-settings"
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
            <v-card-text class="pb-12" v-if="settingsLoaded" style="height: 800px;">
                <v-tabs :show-arrows="false" v-model="tab" background-color="foreground" class="rounded-lg border overflow-hidden">
                    <v-tab>Text</v-tab>
                    <v-tab>Vocabulary box</v-tab>
                    <v-tab>Vocabulary hover box</v-tab>
                </v-tabs>
                <v-tabs-items v-model="tab" elevation="0" class="rounded-lg mt-4 pa-6">
                    <!-- Text section -->
                    <v-tab-item :value="0">
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

                        <!-- Line spacing -->
                        <v-row>
                            <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Space between lines:</v-col>
                            <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                                <v-slider
                                    v-model="settings.lineSpacing"
                                    :tick-labels="['Small', '', '', '', '', '', '', '', '', '', 'Large']"
                                    :tick-size="0"
                                    :max="10"
                                    thumb-label="always"
                                    thumb-size="38"
                                    step="1"
                                    track-color="#c5c5c5"
                                    @change="saveSettings"
                                >
                                </v-slider>
                            </v-col>
                        </v-row>

                        <!-- Maximum text width -->
                        <v-row>
                            <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Maximum text width:</v-col>
                            <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                                <v-slider
                                    v-model="settings.maximumTextWidth"
                                    :tick-labels="['Small', '', '', '', '', '', 'Full']"
                                    :tick-size="0"
                                    :max="6"
                                    thumb-label="always"
                                    thumb-size="38"
                                    step="1"
                                    track-color="#c5c5c5"
                                    @change="saveSettings"
                                >
                                    <template v-slot:thumb-label>{{ maximumTextWidthData[settings.maximumTextWidth] }}</template>
                                </v-slider>
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

                        <!-- Hide all highlighting -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5 ">Hide all highlighting:</v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-switch
                                    color="primary"
                                    v-model="settings.hideAllHighlights" 
                                    @change="saveSettings('hideAllHighlights')"
                                ></v-switch>
                            </v-col>
                        </v-row>

                        <!-- Hide new word highlighting -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5 ">Hide new word highlighting:</v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-switch
                                    color="primary"
                                    v-model="settings.hideNewWordHighlights" 
                                    @change="saveSettings"
                                ></v-switch>
                            </v-col>
                        </v-row>

                        <!-- Vertical text -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Vertical text:</v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-switch
                                    color="primary"
                                    v-model="settings.verticalText" 
                                    @change="saveSettings"
                                    disabled
                                ></v-switch>
                            </v-col>
                        </v-row>

                        <!-- Furigana on highlighted words -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Furigana on highlighted words:</v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-switch
                                    color="primary"
                                    v-model="settings.furiganaOnHighlightedWords" 
                                    @change="saveSettings"
                                ></v-switch>
                            </v-col>
                        </v-row>

                        <!-- Furigana on new words -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Furigana on new words:</v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-switch
                                    color="primary"
                                    v-model="settings.furiganaOnNewWords" 
                                    @change="saveSettings"
                                ></v-switch>
                            </v-col>
                        </v-row>

                        <!-- Auto move words to known -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Auto move words to known:</v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-menu offset-y left nudge-top="-12px">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-icon class="mr-2" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                                    </template>
                                    <v-card outlined class="rounded-lg pa-4" width="320px">
                                        Clicking the <b>Finish reading</b> button moves new words to known.
                                    </v-card>
                                </v-menu>

                                <v-switch
                                    color="primary"
                                    v-model="settings.autoMoveWordsToKnown"
                                    @change="saveSettings"
                                ></v-switch>
                            </v-col>
                        </v-row>

                        <!-- Auto highlight words -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Auto highlight words:</v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-menu offset-y left nudge-top="-12px">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-icon class="mr-2" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                                    </template>
                                    <v-card outlined class="rounded-lg pa-4" width="320px">
                                        Auto highlight words when you add a translation to them.
                                    </v-card>
                                </v-menu>

                                <v-switch
                                    color="primary"
                                    v-model="settings.autoHighlightWords"
                                    @change="saveSettings"
                                ></v-switch>
                            </v-col>
                        </v-row>

                        <!-- Auto level up words -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Auto level up words:</v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-menu offset-y left nudge-top="-12px">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-icon class="mr-2" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                                    </template>
                                    <v-card outlined class="rounded-lg pa-4" width="320px">
                                        Clicking the 'Finished reading' button will automatically level up any words and phrases for which you haven't opened the vocabulary box.
                                    </v-card>
                                </v-menu>

                                <v-switch
                                    color="primary"
                                    v-model="settings.autoLevelUpWords"
                                    @change="saveSettings"
                                ></v-switch>
                            </v-col>
                        </v-row>
                    </v-tab-item>

                    <!-- Vocabulary box section-->
                    <v-tab-item :value="1">
                        <!-- Vocab box scroll into view -->
                        <v-row>
                            <v-col cols="12" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Scroll to vocabulary method:</v-col>
                            <v-col cols="12" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <v-select
                                    v-model="settings.vocabBoxScrollIntoView"
                                    :items="vocabBoxScrollIntoViewData"
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

                        <!-- Vocabulary sidebar -->
                        <v-row>
                            <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5 ">
                                Vocabulary sidebar:
                            </v-col>
                            <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                                <!-- Vocabulary sidebar info box -->
                                <v-menu offset-y left nudge-top="-12px">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-icon class="mr-2" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                                    </template>
                                    <v-card outlined class="rounded-lg pa-4" width="320px">
                                        An always visible sidebar vocabulary in a fixed position, that replaces the popup vocabulary. <br><br>
                                        This option is only available for devices with at least 960px screen width, and it is also only available in subtitle reader if the media controls are hidden. 
                                    </v-card>
                                </v-menu>

                                <v-switch
                                    color="primary"
                                    v-model="settings.vocabularySidebar" 
                                    @change="saveSettings"
                                ></v-switch>
                            </v-col>
                        </v-row>

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
                    </v-tab-item>

                    <!-- Vocabulary hover box section-->
                    <v-tab-item :value="2">
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

                        <!-- Hover vocabulary box dictionary search -->
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
                        <div class="subheader subheader-margin-top d-flex mb-2" v-if="textToSpeechVoices.length">
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
                    </v-tab-item>
                </v-tabs-items>

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

                tab: 0,
                settingsLoaded: false,
                cookieNames: {
                    hideAllHighlights: 'hide-all-highlights',
                    hideNewWordHighlights: 'hide-new-word-highlights',
                    plainTextMode: 'plain-text-mode',
                    fontSize: 'font-size',
                    lineSpacing: 'line-spacing',
                    maximumTextWidth: 'maximum-text-width',
                    autoMoveWordsToKnown: 'auto-move-words-to-known',
                    vocabBoxScrollIntoView: 'vocab-box-scroll-into-view',
                    verticalText: 'vertical-text',
                    furiganaOnHighlightedWords: 'furigana-on-highlighted-words',
                    furiganaOnNewWords: 'furigana-on-new-words',
                    vocabularySidebar: 'vocabulary-sidebar',
                    vocabularyBottomSheet: 'vocabulary-bottom-sheet',
                    vocabularyHoverBox: 'vocabulary-hover-box',
                    vocabularyHoverBoxSearch: 'vocabulary-hover-box-search',
                    vocabularyHoverBoxDelay: 'vocabulary-hover-delay',
                    vocabularyHoverBoxPreferredPosition: 'vocabulary-hover-box-preferred-position',
                    autoHighlightWords: 'auto-highlight-words',
                    autoLevelUpWords: 'auto-level-up-words'
                },
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
                vocabBoxScrollIntoViewData: [
                    {
                        name: 'Disabled',
                        value: 'disabled'
                    },
                    {
                        name: 'Scroll into view',
                        value: 'scroll-into-view'
                    },
                    {
                        name: 'Scroll into view if needed (does not work everywhere)',
                        value: 'scroll-into-view-if-needed'
                    }
                ],
                maximumTextWidthData: ['800px', '900px', '1000px', '1200px', '1400px', '1600px', '100%'],
            }
        },
        props: {
            value : Boolean,
            language: String,
        },
        mounted() {
            this.loadSetting('hideAllHighlights', 'boolean', false);
            this.loadSetting('hideNewWordHighlights', 'boolean', false);
            this.loadSetting('plainTextMode', 'boolean', false);
            this.loadSetting('fontSize', 'integer', 20);
            this.loadSetting('lineSpacing', 'integer', 1);
            this.loadSetting('maximumTextWidth', 'integer', 3);
            this.loadSetting('autoMoveWordsToKnown', 'boolean', true);
            this.loadSetting('vocabBoxScrollIntoView', 'string', 'scroll-into-view');
            this.loadSetting('verticalText', 'string', false);
            this.loadSetting('furiganaOnHighlightedWords', 'boolean', false);
            this.loadSetting('furiganaOnNewWords', 'boolean', false);
            this.loadSetting('vocabularySidebar', 'boolean', true);
            this.loadSetting('vocabularyBottomSheet', 'boolean', true);
            this.loadSetting('vocabularyHoverBox', 'boolean', true);
            this.loadSetting('vocabularyHoverBoxSearch', 'boolean', true);
            this.loadSetting('vocabularyHoverBoxDelay', 'integer', 300);
            this.loadSetting('vocabularyHoverBoxPreferredPosition', 'string', 'bottom');
            this.loadSetting('autoHighlightWords', 'boolean', true);
            this.loadSetting('autoLevelUpWords', 'boolean', false);
            this.settingsLoaded = true;
            this.saveSettings();

            this.textToSpeechVoicesChanged();
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

                this.saveSetting('hideAllHighlights');
                this.saveSetting('hideNewWordHighlights');
                this.saveSetting('plainTextMode');
                this.saveSetting('verticalText');
                this.saveSetting('fontSize');
                this.saveSetting('lineSpacing');
                this.saveSetting('maximumTextWidth');
                this.saveSetting('displaySuggestedTranslations');
                this.saveSetting('autoMoveWordsToKnown');
                this.saveSetting('vocabBoxScrollIntoView');
                this.saveSetting('furiganaOnHighlightedWords');
                this.saveSetting('furiganaOnNewWords');
                this.saveSetting('vocabularySidebar');
                this.saveSetting('vocabularyBottomSheet');
                this.saveSetting('vocabularyHoverBox');
                this.saveSetting('vocabularyHoverBoxSearch');
                this.saveSetting('vocabularyHoverBoxDelay');
                this.saveSetting('vocabularyHoverBoxPreferredPosition');
                this.saveSetting('autoHighlightWords');
                this.saveSetting('autoLevelUpWords');

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
