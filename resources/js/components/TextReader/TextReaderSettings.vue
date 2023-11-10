<template>
    <v-dialog v-model="value" scrollable persistent max-width="800">
        <v-card 
            id="text-reader-settings"
            outlined
            class="rounded-lg"
        >
            <v-card-title>Text reader settings</v-card-title>
            <v-card-text class="pt-6">
                <!-- Line spacing -->
                <v-row>
                    <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Space between lines:</v-col>
                    <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                        <v-slider
                            v-model="lineSpacing"
                            :tick-labels="['Small', '', '', '', '', '', '', '', '', '', 'Large']"
                            :tick-size="0"
                            :max="10"
                            thumb-label="always"
                            thumb-size="38"
                            step="1"
                            track-color="#c5c5c5"
                            @change="settingChanged"
                        >
                        </v-slider>
                    </v-col>
                </v-row>

                <!-- Maximum text width -->
                <v-row>
                    <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Maximum text width:</v-col>
                    <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                        <v-slider
                            v-model="maximumTextWidth"
                            :tick-labels="['Small', '', '', '', '', '', 'Full']"
                            :tick-size="0"
                            :max="5"
                            thumb-label="always"
                            thumb-size="38"
                            step="1"
                            track-color="#c5c5c5"
                            @change="settingChanged"
                        >
                            <template v-slot:thumb-label>{{ maximumTextWidthData[maximumTextWidth] }}</template>
                        </v-slider>
                    </v-col>
                </v-row>

                <!-- Font size -->
                <v-row>
                    <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Font size:</v-col>
                    <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                        <v-slider
                            v-model="fontSize"
                            :tick-labels="['Small', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'Large']"
                            :tick-size="0"
                            :min="12"
                            :max="30"
                            step="1"
                            thumb-label="always"
                            thumb-size="38"
                            track-color="#c5c5c5"
                            @change="settingChanged"
                        ></v-slider>
                    </v-col>
                </v-row>

                <!-- Highlight words -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5 ">Highlight words:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-switch
                            color="primary"
                            v-model="highlightWords" 
                            @change="settingChanged"
                        ></v-switch>
                    </v-col>
                </v-row>

                <!-- Japanese vertical text -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Japanese vertical text:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-switch
                            color="primary"
                            v-model="japaneseText" 
                            @change="settingChanged"
                        ></v-switch>
                    </v-col>
                </v-row>
                
                <!-- Auto move words to known -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5">Auto move words to known:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-switch
                            color="primary"
                            v-model="autoMoveWordsToKnown" 
                            @change="settingChanged"
                        ></v-switch>
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
                maximumTextWidthData: ['800px', '900px', '1000px', '1200px', '1400px', '1600px', '100%'],
                highlightWords: this.$props._highlightWords,
                plainTextMode: this.$props._plainTextMode,
                japaneseText: this.$props._japaneseText,
                fontSize: this.$props._fontSize,
                lineSpacing: this.$props._lineSpacing,
                maximumTextWidth: this.$props._maximumTextWidth,
                displaySuggestedTranslations: this.$props._displaySuggestedTranslations,
                autoMoveWordsToKnown: this.$props._autoMoveWordsToKnown
            }
        },
        props: {
            value : Boolean,
            _highlightWords: Boolean,
            _plainTextMode: Boolean,
            _japaneseText: Boolean,
            _fontSize: Number,
            _lineSpacing: Number,
            _maximumTextWidth: Number,
            _displaySuggestedTranslations: Boolean,
            _autoMoveWordsToKnown: Boolean
        },
        mounted() {
        },
        methods: {
            settingChanged: function() {
                this.$emit('changed', { 
                    'highlightWords': this.highlightWords,
                    'plainTextMode': this.plainTextMode,
                    'japaneseText': this.japaneseText,
                    'fontSize': this.fontSize,
                    'lineSpacing': this.lineSpacing,
                    'maximumTextWidth': this.maximumTextWidth,
                    'autoMoveWordsToKnown': this.autoMoveWordsToKnown
                });
            },  
            close: function() {
                this.$emit('input', false);
            }
        }
    }
</script>
