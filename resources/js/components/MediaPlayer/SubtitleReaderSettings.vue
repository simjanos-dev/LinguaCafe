<template>
    <v-dialog v-model="value" scrollable persistent max-width="800">
        <v-card 
            id="subtitle-reader-settings"
            outlined
            class="rounded-lg"
        >
            <v-card-title>Subtitle reader settings</v-card-title>
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

                <!-- Subtitle block spacing -->
                <v-row>
                    <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Space between subtitles:</v-col>
                    <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                        <v-slider
                            v-model="subtitleBlockSpacing"
                            :tick-size="0"
                            :min="0"
                            :max="5"
                            step="1"
                            thumb-label="always"
                            thumb-size="38"
                            track-color="#c5c5c5"
                            @change="settingChanged"
                        ></v-slider>
                    </v-col>
                </v-row>

                <!-- Maximum text width -->
                <v-row>
                    <v-col cols="12" sm="3" class="d-flex align-center mt-0 mt-md-0 mb-md-5 pb-0 pb-sm-0 pb-md-3">Maximum text width:</v-col>
                    <v-col class="slider-container d-flex pt-xs-0 pt-sm-0 pt-md-3 align-center">
                        <v-slider
                            v-model="maximumTextWidth"
                            :tick-labels="['Small', '', '', '', '', 'Full']"
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

                <!-- Hide all highlighting -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5 ">Hide all highlighting:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-switch
                            color="primary"
                            v-model="hideAllHighlights" 
                            @change="settingChanged('hideAllHighlights')"
                        ></v-switch>
                    </v-col>
                </v-row>

                <!-- Hide new word highlighting -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5 ">Hide new word highlighting:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-switch
                            color="primary"
                            v-model="hideNewWordHighlights" 
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

                <!-- Media player visible -->
                <v-row>
                    <v-col cols="8" md="4" class="switch-container d-flex align-center mt-0 mb-md-5 ">Media player visible:</v-col>
                    <v-col cols="4" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-switch
                            color="primary"
                            v-model="mediaControlsVisible" 
                            @change="settingChanged"
                        ></v-switch>
                    </v-col>
                </v-row>

                <!-- Vocab box scroll into view -->
                <v-row>
                    <v-col cols="12" md="4" class="switch-container d-flex align-center mt-0 ">Scroll to vocabulary method:</v-col>
                    <v-col cols="12" md="8" class="switch-container d-flex align-center mt-0 pt-3 justify-end">
                        <v-select
                            v-model="vocabBoxScrollIntoView"
                            :items="vocabBoxScrollIntoViewData"
                            item-text="name"
                            item-value="value"
                            dense
                            rounded
                            filled
                            hide-details
                            @change="settingChanged"
                        ></v-select>
                    </v-col>
                </v-row>
                <br><br><br>
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
                maximumTextWidthData: ['800px', '1000px', '1200px', '1400px', '1600px', '100%'],
                hideAllHighlights: this.$props._hideAllHighlights,
                hideNewWordHighlights: this.$props._hideNewWordHighlights,
                plainTextMode: this.$props._plainTextMode,
                fontSize: this.$props._fontSize,
                lineSpacing: this.$props._lineSpacing,
                maximumTextWidth: this.$props._maximumTextWidth,
                autoMoveWordsToKnown: this.$props._autoMoveWordsToKnown,
                mediaControlsVisible: this.$props._mediaControlsVisible,
                subtitleBlockSpacing: this.$props._subtitleBlockSpacing,
                vocabBoxScrollIntoView: this.$props._vocabBoxScrollIntoView,
            }
        },
        props: {
            value: Boolean,
            _hideAllHighlights: Boolean,
            _hideNewWordHighlights: Boolean,
            _plainTextMode: Boolean,
            _fontSize: Number,
            _lineSpacing: Number,
            _maximumTextWidth: Number,
            _autoMoveWordsToKnown: Boolean,
            _mediaControlsVisible: Boolean,
            _subtitleBlockSpacing: Number,
            _vocabBoxScrollIntoView: String
        },
        mounted() {
        },
        methods: {
            settingChanged(settingName = '') {
                if (settingName == 'hideAllHighlights') {
                    this.hideNewWordHighlights = this.hideAllHighlights;
                }

                this.$emit('changed', { 
                    'hideAllHighlights': this.hideAllHighlights,
                    'hideNewWordHighlights': this.hideNewWordHighlights,
                    'plainTextMode': this.plainTextMode,
                    'fontSize': this.fontSize,
                    'lineSpacing': this.lineSpacing,
                    'maximumTextWidth': this.maximumTextWidth,
                    'autoMoveWordsToKnown': this.autoMoveWordsToKnown,
                    'mediaControlsVisible': this.mediaControlsVisible,
                    'subtitleBlockSpacing': this.subtitleBlockSpacing,
                    'vocabBoxScrollIntoView': this.vocabBoxScrollIntoView
                });
            },  
            close() {
                this.$emit('input', false);
            }
        }
    }
</script>
