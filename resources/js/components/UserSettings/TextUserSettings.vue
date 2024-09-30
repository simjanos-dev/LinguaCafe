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
                        @change="buttonSelected"
                        title="Word count display type"
                    >
                        <!-- Level buttons -->
                        <v-btn small class="px-1" v-for="(level, levelIndex) in levels" :key="`level-${levelIndex}`">
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
                        @change="buttonSelected"
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
                            <v-menu offset-y>
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
                            <v-menu offset-y>
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
                            <v-menu offset-y>
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
                    <div class="w-100 mt-12">
                        <div class="d-block" style="height: 200px;" :key="'sample-' + settingsKey">
                            <div 
                                v-for="(word, wordIndex) in sampleText.split(' ')"
                                class="d-inline-block" 
                                :style="wordIndex === 4 ? highlightedStyling : { 'margin-right': '1px'}"
                            >
                                {{ word + ' ' }}
                            </div>
                        </div>
                    </div>
            </v-container>
        </v-card>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                settingsKey: 0,
                selectedLevelIndex: 0,
                selectedThemeIndex: 0,
                sampleText: 'LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . LinguaCafe is a language learning platform , where you can read foreign texts . ',
                wordStyling: null,
                themes: ['light', 'dark', 'eink'],
                levels: ['new', 'level1', 'level2', 'level3', 'level4', 'level5', 'level6', 'level7'],
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
            this.buildWordStylingSettingsData()
        },
        methods: {

            colorChanged(color, colorName) {
                this.wordStyling[this.selectedTheme][this.selectedLevel][colorName] = color
            },
            updateSampleTextStyling() {
                this.highlightedStyling = {
                    'border-width': this.wordStyling[this.selectedTheme][this.selectedLevel].borderWidth + 'px',
                    'border-color': this.wordStyling[this.selectedTheme][this.selectedLevel].borderColor,
                    'border-style': this.wordStyling[this.selectedTheme][this.selectedLevel].borderStyle,
                    'background-color': this.wordStyling[this.selectedTheme][this.selectedLevel].backgroundColor,
                    'border-radius': this.wordStyling[this.selectedTheme][this.selectedLevel].borderRadius + 'px',
                    'margin-right': '1px',
                }

                // remove top border
                if (!this.wordStyling[this.selectedTheme][this.selectedLevel].borderTop) {
                    this.highlightedStyling.borderTop = '1px solid transparent'
                }

                // remove bottom border
                if (!this.wordStyling[this.selectedTheme][this.selectedLevel].borderBottom) {
                    this.highlightedStyling.borderBottom = '1px solid transparent'
                }

                // remove side borders
                if (!this.wordStyling[this.selectedTheme][this.selectedLevel].borderSides) {
                    this.highlightedStyling.borderLeft = '1px solid transparent'
                    this.highlightedStyling.borderRight = '1px solid transparent'
                }

                this.settingsKey ++
            },
            buttonSelected() {
                console.log('selected', this.selectedLevel, this.selectedTheme)
            },
            buildWordStylingSettingsData() {
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
                            backgroundColor: '#ff0000',
                            textColor: '#ff0000',
                        }
                    });
                });

                console.log('wordStyling', this.wordStyling)
            }
        }
    }
</script>
