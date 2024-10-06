<template>
    <div>
        <!-- Text header -->
        <div class="subheader mt-4 d-flex">
            Text
        </div>

        <!-- Text content -->
        <v-card outlined class="rounded-lg mt-2" :key="'settings' + settingsKey" :loading="loading">
            <v-container class="pa-8" v-if="textStyling">
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

                <!-- Horizontal padding -->
                <div class="w-100">
                    <label class="mb-0">
                        Horizontal padding
                    </label>
                    <v-slider
                        v-model="textStyling[selectedTheme][selectedLevel].paddingHorizontal"
                        max="16"
                        min="0"
                        hide-details
                        thumb-label="always"
                        :thumb-size="24"
                        @change="updateSampleTextStyling"
                    ></v-slider>
                </div>

                <!-- Top padding -->
                <div class="w-100">
                    <label class="mb-0">
                        Top padding
                    </label>
                    <v-slider
                        v-model="textStyling[selectedTheme][selectedLevel].paddingTop"
                        max="8"
                        min="0"
                        hide-details
                        thumb-label="always"
                        :thumb-size="24"
                        @change="updateSampleTextStyling"
                    ></v-slider>
                </div>

                <!-- Bottom padding -->
                <div class="w-100">
                    <label class="mb-0">
                        Bottom padding
                    </label>
                    <v-slider
                        v-model="textStyling[selectedTheme][selectedLevel].paddingBottom"
                        max="8"
                        min="0"
                        hide-details
                        thumb-label="always"
                        :thumb-size="24"
                        @change="updateSampleTextStyling"
                    ></v-slider>
                </div>

                <!-- Padding settings -->
                <div class="w-100 mb-0">
                    <label class="w-100 mb-0">
                        Horizontal padding
                    </label>
                    <v-checkbox
                        v-model="textStyling[selectedTheme][selectedLevel].horizontalPaddingSpacelessLanguagesOnly"
                        hide-details
                        density="compact"
                        class="d-inline-block mt-0"
                        label="For spaceless languages only"
                        @change="updateSampleTextStyling"
                    >
                    </v-checkbox>
                </div>
                
                <!-- Border width -->
                <div class="w-100 mt-3">
                    <label class="mb-0">
                        Border and wave width
                    </label>
                    <v-slider
                        v-model="textStyling[selectedTheme][selectedLevel].borderWidth"
                        max="8"
                        min="0"
                        hide-details
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
                        v-model="textStyling[selectedTheme][selectedLevel].borderRadius"
                        max="32"
                        min="0"
                        hide-details
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
                        v-model="textStyling[selectedTheme][selectedLevel].borderStyle"
                        label="Border type"
                        rounded
                        dense
                        filled
                        single-line
                        hide-details
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
                <div class="w-100 mb-0 mt-2">
                    <label class="w-100 mb-0">
                        Border positions
                    </label>
                    <v-checkbox
                        v-model="textStyling[selectedTheme][selectedLevel].borderTop"
                        hide-details
                        density="compact"
                        class="d-inline-block mt-0"
                        label="Top"
                        @change="updateSampleTextStyling"
                    >
                    </v-checkbox>
                    <v-checkbox
                        v-model="textStyling[selectedTheme][selectedLevel].borderBottom"
                        hide-details
                        density="compact"
                        class="d-inline-block mt-0 ml-2"
                        label="Bottom"
                        @change="updateSampleTextStyling"
                    >
                    </v-checkbox>
                    <v-checkbox
                        v-model="textStyling[selectedTheme][selectedLevel].borderSides"
                        hide-details
                        density="compact"
                        class="d-inline-block mt-0 ml-2"
                        label="Sides"
                        @change="updateSampleTextStyling"
                    >
                    </v-checkbox>
                </div>

                <!-- Font -->
                <div class="w-100 mb-0 mt-2">
                    <label class="w-100 mb-0">
                        Font
                    </label>
                    <v-checkbox
                        v-model="textStyling[selectedTheme][selectedLevel].bold"
                        hide-details
                        density="compact"
                        class="d-inline-block mt-0"
                        label="Bold"
                        @change="updateSampleTextStyling"
                    >
                    </v-checkbox>
                    <v-checkbox
                        v-model="textStyling[selectedTheme][selectedLevel].italic"
                        hide-details
                        density="compact"
                        class="d-inline-block mt-0 ml-2"
                        label="Italic"
                        @change="updateSampleTextStyling"
                    >
                    </v-checkbox>
                    <v-checkbox
                        v-model="textStyling[selectedTheme][selectedLevel].wavyUnderline"
                        hide-details
                        density="compact"
                        class="d-inline-block mt-0 ml-2"
                        label="Wavy underline (removes borders)"
                        @change="updateSampleTextStyling"
                    >
                    </v-checkbox>
                </div>

                <!-- Colors table -->
                <v-simple-table class="rounded-lg no-hover border mt-2" v-if="!loading">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Color</th>
                            <th>Hex</th>
                            <th>Reset</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Border color</td>
                            <td>
                                <v-menu offset-y :close-on-content-click="false">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-card
                                            class="border mx-auto"
                                            outlined
                                            width="48px"
                                            height="26px"
                                            :color="textStyling[selectedTheme][selectedLevel].borderColor"
                                            depressed
                                            v-on="on"
                                        >
                                        
                                        </v-card>
                                    </template>
                                    <v-color-picker
                                        :value="textStyling[selectedTheme][selectedLevel].borderColor"
                                        @input="colorChanged($event, 'borderColor')"
                                    />
                                </v-menu>
                            </td>
                            <td>
                                <v-text-field
                                    class="my-2"
                                    v-model="textStyling[selectedTheme][selectedLevel].borderColor"
                                    ref="colorHex"
                                    filled
                                    rounded
                                    dense
                                    hide-details
                                    maxlength="7"
                                ></v-text-field>
                            </td>
                            <td>
                                <v-btn
                                    icon
                                    title="Restore default"
                                    @click="resetColor('borderColor')"
                                >
                                    <v-icon>mdi-restore</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                        <tr>
                            <td>Background color</td>
                            <td>
                                <v-menu offset-y :close-on-content-click="false">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-card
                                            class="border mx-auto"
                                            outlined
                                            width="48px"
                                            height="26px"
                                            :color="textStyling[selectedTheme][selectedLevel].backgroundColor"
                                            depressed
                                            v-on="on"
                                        >
                                        
                                        </v-card>
                                    </template>
                                    <v-color-picker
                                        :value="textStyling[selectedTheme][selectedLevel].backgroundColor"
                                        @input="colorChanged($event, 'backgroundColor')"
                                    />
                                </v-menu>
                            </td>
                            <td>
                                <v-text-field
                                    class="my-2"
                                    v-model="textStyling[selectedTheme][selectedLevel].backgroundColor"
                                    ref="colorHex"
                                    filled
                                    rounded
                                    dense
                                    hide-details
                                    maxlength="7"
                                ></v-text-field>
                            </td>
                            <td>
                                <v-btn
                                    icon
                                    title="Restore default"
                                    @click="resetColor('backgroundColor')"
                                >
                                    <v-icon>mdi-restore</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                        <tr>
                            <td>Text color</td>
                            <td>
                                <v-menu offset-y :close-on-content-click="false">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-card
                                            class="border mx-auto"
                                            outlined
                                            width="48px"
                                            height="26px"
                                            :color="textStyling[selectedTheme][selectedLevel].textColor"
                                            depressed
                                            v-on="on"
                                        >
                                        
                                        </v-card>
                                    </template>
                                    <v-color-picker
                                        :value="textStyling[selectedTheme][selectedLevel].textColor"
                                        @input="colorChanged($event, 'textColor')"
                                    />
                                </v-menu>
                            </td>
                            <td>
                                <v-text-field
                                    class="my-2"
                                    v-model="textStyling[selectedTheme][selectedLevel].textColor"
                                    ref="colorHex"
                                    filled
                                    rounded
                                    dense
                                    hide-details
                                    maxlength="7"
                                ></v-text-field>
                            </td>
                            <td>
                                <v-btn
                                    icon
                                    title="Restore default"
                                    @click="resetColor('textColor')"
                                >
                                    <v-icon>mdi-restore</v-icon>
                                </v-btn>
                            </td>
                        </tr>
                    </tbody>
                </v-simple-table>
            
                <!-- Sample text -->
                <div class="w-100 mt-12" :key="sampleTextKey" :style="highlightedStyling">
                    <div class="d-block sample-text" :key="'sample-' + settingsKey">
                        <div 
                            v-for="(word, wordIndex) in sampleText"
                            :key="'sample-text-word-' + wordIndex"
                            :class="[
                                'd-inline-block', 
                                'word',
                                word.spaceAfter ? 'space-after' : '',
                                word.phrase ? 'phrase' : '',
                            ]"
                            :stage="word.stage"
                        >
                            {{ word.word }}
                        </div>
                    </div>
                </div>

                <v-btn @click="test">Console log settings</v-btn>
            </v-container>
        </v-card>
    </div>
</template>


<!-- 
    This is a separate setting from the theme colors, because I can only store colors in vuetify, 
    however there are other settings here like borders and paddings.
-->
<script>
    import defaultTextThemes from './../../textThemes';
    import ThemeService from './../../services/ThemeService';

    export default {
        data: function() {
            return {
                loading: false,
                settingsKey: 0,
                sampleTextKey: 0,
                selectedLevelIndex: 0,
                selectedThemeIndex: 0,
                sampleText: [],
                textStyling: null,
                themes: ['light', 'dark', 'eink'],
                levels: ['known', 'new', 'level1', 'level2', 'level3', 'level4', 'level5', 'level6', 'level7', 'ignored'],
                /*
                    On this page I used displayed level names. This object maps displayed level names to names that are used in css.
                */
                levelMapping: {
                    'level1': 'level-1',
                    'level2': 'level-2',
                    'level3': 'level-3',
                    'level4': 'level-4',
                    'level5': 'level-5',
                    'level6': 'level-6',
                    'level7': 'level-7',
                    'known': 'level0',
                    'ignored': 'level1',
                    'new': 'level2',
                },
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
            this.buildSampleText()
            this.loadInitialtextStylingSettingsData()
            this.updateSampleTextStyling()

            this.$emit('update', this.textStyling)
        },
        methods: {
            test() {
                console.log(this.textStyling)
            },
            resetColor(colorName) {
                console.log('default color', defaultTextThemes[this.selectedTheme][this.selectedLevel][colorName])
                this.textStyling[this.selectedTheme][this.selectedLevel][colorName] = defaultTextThemes[this.selectedTheme][this.selectedLevel][colorName];
                this.updateSampleTextColors()
            },
            colorChanged(color, colorName) {
                this.textStyling[this.selectedTheme][this.selectedLevel][colorName] = color
                this.updateSampleTextColors()
            },
            // updates the currently selected theme/word level settings
            updateSampleTextStyling() {
                this.highlightedStyling = {}
                
                this.levels.forEach((level) => {
                    Object.assign(this.highlightedStyling, this.getCssSettingObject(this.selectedTheme, level))
                })
                
                this.settingsKey ++

                this.$emit('update', this.textStyling)
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
                        Object.assign(settings[theme], this.getCssSettingObject(theme, level))
                    })
                })

                return settings
            },
            // returns an object with css styling for a single theme/word level combination
            getCssSettingObject(theme, wordLevel) {
                let cssVariables = {}

                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-color`] = this.textStyling[theme][wordLevel].textColor;
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-background-color`] = this.textStyling[theme][wordLevel].backgroundColor;
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-width`] = this.textStyling[theme][wordLevel].borderWidth + 'px';
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-color`] = this.textStyling[theme][wordLevel].borderColor;
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-style`] = this.textStyling[theme][wordLevel].borderStyle;
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-radius`] = this.textStyling[theme][wordLevel].borderRadius + 'px';

                // padding
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-padding-top`] = this.textStyling[theme][wordLevel].paddingTop + 'px';
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-padding-bottom`] = this.textStyling[theme][wordLevel].paddingBottom + 'px';
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-padding-left`] = this.textStyling[theme][wordLevel].paddingHorizontal + 'px';
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-padding-right`] = this.textStyling[theme][wordLevel].paddingHorizontal + 'px';
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-spaceless-language-padding-left`] = this.textStyling[theme][wordLevel].paddingHorizontal + 'px';
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-spaceless-language-padding-right`] = this.textStyling[theme][wordLevel].paddingHorizontal + 'px';

                // horizontal padding for spaceless languages only
                if (this.textStyling[theme][wordLevel].horizontalPaddingSpacelessLanguagesOnly) {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-padding-left`] = '0px';
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-padding-right`] = '0px';
                }
                
                // add colors 
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-color`] = this.textStyling[theme][wordLevel].borderColor;
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-background-color`] = this.textStyling[theme][wordLevel].backgroundColor;
                cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-color`] = this.textStyling[theme][wordLevel].textColor;

                // remove top border
                if (!this.textStyling[theme][wordLevel].borderTop || !this.textStyling[theme][wordLevel].borderWidth) {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-top-width`] = '0px'
                } else {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-top-width`] = this.textStyling[theme][wordLevel].borderWidth + 'px';
                }

                // remove bottom border
                if (!this.textStyling[theme][wordLevel].borderBottom || !this.textStyling[theme][wordLevel].borderWidth) {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-bottom-width`] = '0px'
                } else {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-bottom-width`] = this.textStyling[theme][wordLevel].borderWidth + 'px';
                }

                // remove side borders
                if (!this.textStyling[theme][wordLevel].borderSides || !this.textStyling[theme][wordLevel].borderWidth) {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-left-width`] = '0px'
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-right-width`] = '0px'
                } else {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-left-width`] = this.textStyling[theme][wordLevel].borderWidth + 'px';
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-right-width`] = this.textStyling[theme][wordLevel].borderWidth + 'px';
                }

                // add bold styling
                if (this.textStyling[theme][wordLevel].bold) {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-weight`] = 'bold'
                } else {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-weight`] = 'normal'
                }

                // add italic styling
                if (this.textStyling[theme][wordLevel].italic) {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-style`] = 'italic'
                } else {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-style`] = 'normal'
                }

                // add wavy underline
                if (this.textStyling[theme][wordLevel].wavyUnderline) {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-text-decoration`] = 'underline'
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-bottom-width`] = '0px'
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-top-width`] = '0px'
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-left-width`] = '0px'
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-border-right-width`] = '0px'
                } else {
                    cssVariables[`--interactive-text-${this.levelMapping[wordLevel]}-text-decoration`] = 'none'
                }
                

                return cssVariables
            },
            updateSampleTextColors() {
                this.highlightedStyling[`--interactive-text-${this.levelMapping[this.selectedLevel]}-border-color`] = this.textStyling[this.selectedTheme][this.selectedLevel].borderColor;
                this.highlightedStyling[`--interactive-text-${this.levelMapping[this.selectedLevel]}-background-color`] = this.textStyling[this.selectedTheme][this.selectedLevel].backgroundColor;
                this.highlightedStyling[`--interactive-text-${this.levelMapping[this.selectedLevel]}-color`] = this.textStyling[this.selectedTheme][this.selectedLevel].textColor;

                this.sampleTextKey ++
            },
            buildSampleText() {
                this.sampleText = []

                // generate plain text
                let plainText = 'LinguaCafe is a foreign language reading platform , and this is a sample text . '
                let plainTextLength = 5;
                for (let i = 0; i < plainTextLength; i++) {
                    plainText += plainText
                }

                // generate object based sample text
                let textArray = plainText.split(' ')
                textArray.forEach((word, wordIndex) => {
                    let tempWord = {
                        word: word,
                        spaceAfter: true,
                        stage: 0,
                        phrase: false,
                    }

                    // remove space from previous word if current word is a point or a comma
                    if (wordIndex && [',', '.'].includes(tempWord.word)) {
                        this.sampleText[wordIndex - 1].spaceAfter = false
                    }

                    // highlight random words
                    if (Math.random() * 100 < 40) {
                        tempWord.stage = Math.floor(Math.random() * 7 + 1) * -1
                    }

                    // set random words to new
                    if (Math.random() * 100 < 16) {
                        tempWord.stage = 2
                    }

                    // set random words to ignored
                    if (Math.random() * 100 < 16) {
                        tempWord.stage = 1
                    }

                    // create a phrase
                    if (wordIndex >= 3 && wordIndex <= 6) {
                        tempWord.stage = 0
                        tempWord.phrase = true
                    }


                    this.sampleText.push(tempWord)
                })
            },
            loadInitialtextStylingSettingsData() {
                this.textStyling = JSON.parse(JSON.stringify(defaultTextThemes))
                this.loading = true

                axios.post('/settings/user/get', {settingNames: ['textStyling']}).then((response) => {
                    console.log('textStyling loaded')
                    if (response.data.textStyling) {
                        this.textStyling = response.data.textStyling
                    }
                    
                    this.updateSampleTextStyling()
                    this.loading = false
                })
            }
        }
    }
</script>
