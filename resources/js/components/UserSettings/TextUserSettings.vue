<template>
    <div>
        <!-- Text header -->
        <div class="subheader mt-4 d-flex">
            Text
        </div>

        <!-- Text content -->
        <v-card outlined class="rounded-lg mt-2" :key="'settings' + settingsKey">
            <v-container class="pa-8" v-if="wordStyling">
                <div class="w-100 d-flex mb-4">
                    <!-- Level buttons -->
                    <v-btn-toggle
                        v-model="selectedLevelIndex"
                        mandatory
                        rounded
                        dense
                        title="Word count display type"
                    >
                        <!-- Level buttons -->
                        <v-btn small :class="[level.length > 1 ? 'px-3' : 'px-1']" v-for="(level, levelIndex) in levels" :key="`level-${levelIndex}`">
                            {{ level.replace('level', '') }}
                        </v-btn>
                    </v-btn-toggle>
                    <v-spacer />

                    <!-- Theme buttons -->
                    <v-btn-toggle
                        v-model="selectedThemeIndex"
                        mandatory
                        rounded
                        dense
                        title="Word count display type"
                    >
                        <v-btn small class="px-1" v-for="(theme, themeIndex) in themes" :key="`theme-${themeIndex}`">
                            {{ theme }}
                        </v-btn>
                    </v-btn-toggle>
                </div>
                <div class="w-100">
                        <!-- Border width -->
                        <div class="w-100">
                            <label class="mb-0">
                                Border width
                            </label>
                            <v-slider
                                v-model="wordStyling[selectedTheme][selectedLevel].borderWidth"
                                max="8"
                                min="0"
                                thumb-label="always"
                                :thumb-size="24"
                                @change="updateSampleTextStyling"
                            ></v-slider>
                        </div>

                        <!-- Border radius -->
                        <div class="w-100">
                            <label class="mb-0">
                                Border radius
                            </label>
                            <v-slider
                                v-model="wordStyling[selectedTheme][selectedLevel].borderRadius"
                                max="32"
                                min="0"
                                thumb-label="always"
                                :thumb-size="24"
                                @change="updateSampleTextStyling"
                            ></v-slider>
                        </div>

                        <!-- Border type -->
                        <div class="w-100">
                            <label class="mb-0">
                                Border type
                            </label>
                            <v-select
                                v-model="wordStyling[selectedTheme][selectedLevel].borderStyle"
                                label="Border type"
                                rounded
                                dense
                                filled
                                single-line
                                :items="[
                                    'solid',
                                    'double',
                                    'dotted',
                                    'dashed',
                                ]"
                                @change="updateSampleTextStyling"
                            ></v-select>
                        </div>

                        <!-- Border positions -->
                        <div class="w-100 mb-0">
                            <label class="w-100 mb-0">
                                Border positions
                            </label>
                            <v-checkbox
                                v-model="wordStyling[selectedTheme][selectedLevel].borderTop"
                                hide-details
                                density="compact"
                                class="d-inline-block mt-0"
                                label="Top"
                                @change="updateSampleTextStyling"
                            >
                            </v-checkbox>
                            <v-checkbox
                                v-model="wordStyling[selectedTheme][selectedLevel].borderBottom"
                                hide-details
                                density="compact"
                                class="d-inline-block mt-0 ml-2"
                                label="Bottom"
                                @change="updateSampleTextStyling"
                            >
                            </v-checkbox>
                            <v-checkbox
                                v-model="wordStyling[selectedTheme][selectedLevel].borderSides"
                                hide-details
                                density="compact"
                                class="d-inline-block mt-0 ml-2"
                                label="Sides"
                                @change="updateSampleTextStyling"
                            >
                            </v-checkbox>
                        </div>

                        <!-- Border color -->
                        <div class="w-100 mt-1 mb-2">
                            <label class="mb-0">
                                Border color
                            </label>
                            <v-menu offset-y :close-on-content-click="false">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn
                                        small
                                        class="d-block"
                                        v-bind="attrs"
                                        :color="wordStyling[selectedTheme][selectedLevel].borderColor"
                                        depressed
                                        v-on="on"
                                    >
                                    
                                    </v-btn>
                                </template>
                                <v-color-picker
                                    :value="wordStyling[selectedTheme][selectedLevel].borderColor"
                                    @input="colorChanged($event, 'borderColor')"
                                />
                            </v-menu>
                        </div>

                        <!-- Background color -->
                        <div class="w-100 mb-2">
                            <label class="mb-1">
                                Background color
                            </label>
                            <v-menu offset-y :close-on-content-click="false">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn
                                        small
                                        class="d-block"
                                        v-bind="attrs"
                                        :color="wordStyling[selectedTheme][selectedLevel].backgroundColor"
                                        depressed
                                        v-on="on"
                                    >
                                    
                                    </v-btn>
                                </template>
                                <v-color-picker
                                    :value="wordStyling[selectedTheme][selectedLevel].backgroundColor"
                                    @input="colorChanged($event, 'backgroundColor')"
                                />
                            </v-menu>
                        </div>

                        <!-- Text color -->
                        <div class="w-100 mb-2">
                            <label class="mb-1">
                                Text color
                            </label>
                            <v-menu offset-y :close-on-content-click="false">
                                <template v-slot:activator="{ on, attrs }">
                                    <v-btn
                                        small
                                        class="d-block"
                                        v-bind="attrs"
                                        :color="wordStyling[selectedTheme][selectedLevel].textColor"
                                        depressed
                                        v-on="on"
                                    >
                                    
                                    </v-btn>
                                </template>
                                <v-color-picker
                                    :value="wordStyling[selectedTheme][selectedLevel].textColor"
                                    @input="colorChanged($event, 'textColor')"
                                />
                            </v-menu>
                        </div>
                    </div>
                
                    <!-- Sample text -->
                    <div class="w-100 mt-12" :key="sampleTextKey" :style="highlightedStyling">
                        <div class="d-block sample-text" :key="'sample-' + settingsKey">
                            <div 
                                v-for="(word, wordIndex) in sampleText.split(' ')"
                                class="d-inline-block word space-after"
                            >
                                {{ word + ' ' }}
                            </div>
                        </div>
                    </div>

                    <v-btn @click="test">test</v-btn>
            </v-container>
        </v-card>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                settingsKey: 0,
                sampleTextKey: 0,
                selectedLevelIndex: 0,
                selectedThemeIndex: 0,
                borderColor: '',
                backgroundColor: '',
                textColor: '',
                sampleText: 'LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . ',
                wordStyling: null,
                themes: ['light', 'dark', 'eink'],
                levels: ['known', 'new', 'level1', 'level2', 'level3', 'level4', 'level5', 'level6', 'level7', 'ignored'],
                highlightedStyling: {},
            }
        },
        computed: {
            selectedTheme: function(){
                return this.themes[this.selectedThemeIndex]
            },
            selectedLevel: function(){
                return this.levels[this.selectedLevelIndex]
            }
        },
        watch: {
            selectedThemeIndex: {
                handler: function() {
                    this.updateSampleTextStyling()
                },
            },
            selectedLevelIndex: {
                handler: function() {
                    this.updateSampleTextStyling()
                },
            },
        },
        mounted() {
            this.buildInitialWordStylingSettingsData()
            this.updateSampleTextStyling()
        },
        methods: {
            test() {
                console.log('css settings', this.getTextStylingSettingsObject())
            },
            colorChanged(color, colorName) {
                this.wordStyling[this.selectedTheme][this.selectedLevel][colorName] = color
                this.updateSampleTextColors()
            },
            // updates the currently selected theme/word level settings
            updateSampleTextStyling() {
                this.highlightedStyling = this.getCssSettingObject(this.selectedTheme, this.selectedLevel)
                this.settingsKey ++
            },
            /*
                Returns an object that can be injected into an html element as a style attribute. 
                It contains the styling for every theme and word level combinations
            */
            getTextStylingSettingsObject() {
                let settings = {}
                this.themes.forEach((theme) => {
                    settings[theme] = {}
                    this.levels.forEach((level) => {
                        settings[theme][level] = this.getCssSettingObject(theme, level)
                    })
                })

                return settings
            },
            // returns an object with css styling for a single theme/word level combination
            getCssSettingObject(theme, wordLevel) {
                let cssVariables = {
                    '--interactive-text-color': this.wordStyling[theme][wordLevel].textColor,
                    '--interactive-text-background-color': this.wordStyling[theme][wordLevel].backgroundColor,
                    '--interactive-text-border-width': this.wordStyling[theme][wordLevel].borderWidth + 'px',
                    '--interactive-text-border-color': this.wordStyling[theme][wordLevel].borderColor,
                    '--interactive-text-border-style': this.wordStyling[theme][wordLevel].borderStyle,
                    '--interactive-text-border-radius': this.wordStyling[theme][wordLevel].borderRadius + 'px',
                    '--interactive-text-padding-top' : '0px',
                    '--interactive-text-padding-bottom' : '0px',
                    '--interactive-text-padding-left' : '0px',
                    '--interactive-text-padding-right' : '0px',
                }

                // add colors 
                cssVariables['--interactive-text-border-color'] = this.wordStyling[theme][wordLevel].borderColor;
                cssVariables['--interactive-text-background-color'] = this.wordStyling[theme][wordLevel].backgroundColor;
                cssVariables['--interactive-text-color'] = this.wordStyling[theme][wordLevel].textColor;

                // remove top border
                if (!this.wordStyling[theme][wordLevel].borderTop || !this.wordStyling[theme][wordLevel].borderWidth) {
                    cssVariables['--interactive-text-padding-top'] = '1px'
                    cssVariables['--interactive-text-border-top'] = '0px'
                } else {
                    cssVariables['--interactive-text-border-top'] = this.wordStyling[theme][wordLevel].borderWidth + 'px';
                }

                // remove bottom border
                if (!this.wordStyling[theme][wordLevel].borderBottom || !this.wordStyling[theme][wordLevel].borderWidth) {
                    cssVariables['--interactive-text-padding-bottom'] = '1px'
                    cssVariables['--interactive-text-border-bottom'] = '0px'
                } else {
                    cssVariables['--interactive-text-border-bottom'] = this.wordStyling[theme][wordLevel].borderWidth + 'px';
                }

                // remove side borders
                if (!this.wordStyling[theme][wordLevel].borderSides || !this.wordStyling[theme][wordLevel].borderWidth) {
                    cssVariables['--interactive-text-padding-left'] = '1px'
                    cssVariables['--interactive-text-border-left'] = '0px'
                    cssVariables['--interactive-text-padding-right'] = '1px'
                    cssVariables['--interactive-text-border-right'] = '0px'
                } else {
                    cssVariables['--interactive-text-border-left'] = this.wordStyling[theme][wordLevel].borderWidth + 'px';
                    cssVariables['--interactive-text-border-right'] = this.wordStyling[theme][wordLevel].borderWidth + 'px';
                }

                return cssVariables
            },
            updateSampleTextColors() {
                this.highlightedStyling['--interactive-text-border-color'] = this.wordStyling[this.selectedTheme][this.selectedLevel].borderColor;
                this.highlightedStyling['--interactive-text-background-color'] = this.wordStyling[this.selectedTheme][this.selectedLevel].backgroundColor;
                this.highlightedStyling['--interactive-text-color'] = this.wordStyling[this.selectedTheme][this.selectedLevel].textColor;

                this.sampleTextKey ++
            },
            buildInitialWordStylingSettingsData() {
                this.wordStyling = {}
                this.themes.forEach((theme) => {
                    this.wordStyling[theme] = {};
                    this.levels.forEach((level) => {
                        this.wordStyling[theme][level] = {
                            borderWidth: 0,
                            borderRadius: 0,
                            borderStyle: 'solid',
                            borderTop: true,
                            borderBottom: true,
                            borderSides: true,
                            borderColor: '#ff0000',
                            backgroundColor: '#28272C',
                            textColor: '#CACACA',
                        }
                    });
                });
            }
        }
    }
</script>
