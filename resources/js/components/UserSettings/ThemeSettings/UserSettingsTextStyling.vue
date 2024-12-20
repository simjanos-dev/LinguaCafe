<template>
    <div id="text-user-settings">
        <!-- Reset text styling dialog -->
        <reset-text-styling-dialog v-model="resetTextStylingDialog" @reset="resetDefaultTextStyling"/>

        <!-- Text header -->
        <div class="subheader mt-4 d-flex">
            Text
        </div>

        <!-- Text content -->
        <v-card outlined class="rounded-lg mt-2" :loading="loading">
            <v-container class="pa-8" v-if="textStyling">
                <!-- Switch buttons (small screen) -->
                <div id="option-select-inputs" class="w-100 d-flex justify-space-between flex-wrap mb-4">
                    <div class="text-option-input">
                        <label class="mb-0">
                            Word level
                        </label>
                        <v-select
                            :value="selectedLevel"
                            label="Level"
                            rounded
                            dense
                            filled
                            single-line
                            hide-details
                            :items="levels"
                            @change="selectedLevelInputChanged"
                        ></v-select>
                    </div>

                    <div class="text-option-input">
                        <label class="mb-0 mt-4">
                            Theme
                        </label>
                        <v-select
                            label="Theme"
                            :value="selectedTheme"
                            rounded
                            dense
                            filled
                            single-line
                            hide-details
                            :items="themes"
                            @change="selectedThemeInputChanged"
                        ></v-select>
                    </div>
                </div>

                <!-- Horizontal padding -->
                <div class="w-100">
                    <label class="mb-0 mt-4">
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
                    <label class="mb-0 mt-4">
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
                    <label class="mb-0 mt-4">
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
                        <v-menu offset-y nudge-top="-12px">
                            <template v-slot:activator="{ on, attrs }">
                                <v-icon class="ml-1" v-bind="attrs" v-on="on">mdi-help-circle</v-icon>
                            </template>
                            <v-card outlined class="rounded-lg pa-4" width="320px">
                                Some languages like Chinese, Japanese and Thai do not have spaces between words. This option is for users who prefer having padding 
                                only for these languages to improve readability, while disabling padding for languages that do have spaces between words to avoid words
                                slightly moving on the screen when a word's level was changed.
                            </v-card>
                        </v-menu>
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
                <div class="w-100">
                    <label class="mb-0 mt-4">
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
                    <label class="mb-0 mt-4">
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
                    <label class="mb-0 mt-4">
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
                <div class="w-100 mb-0">
                    <label class="w-100 mb-0 mt-4">
                        Border positions
                    </label>
                    <div id="border-positions" class="d-flex">
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
                </div>

                <!-- Font -->
                <div class="w-100 mb-0">
                    <label class="w-100 mb-0 mt-4">
                        Font
                    </label>
                    <div id="font-options" class="d-flex">
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
                </div>

                <!-- Colors table -->
                <v-simple-table class="rounded-lg no-hover border mt-4" v-if="!loading">
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
                                    @input="updateSampleTextStyling"
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
                                    @input="updateSampleTextStyling"
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
                                    @input="updateSampleTextStyling"
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
                            <td>Background transparency</td>
                            <td colspan="3">
                                <div class="px-2">
                                    <v-slider
                                        v-model="textStyling[selectedTheme][selectedLevel].backgroundTransparency"
                                        max="100"
                                        min="0"
                                        step="1"
                                        hide-details
                                        thumb-label="always"
                                        :thumb-size="24"
                                        @change="updateSampleTextStyling"
                                    ></v-slider>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </v-simple-table>
            
                <!-- Sample text -->
                 <div :style="highlightedStyling">
                    <user-settings-text-styling-sample />
                </div>

                <v-btn rounded depressed color="primary" @click="showResetTextStylingDialog">Reset</v-btn>
                <v-btn rounded depressed color="primary" @click="logTextStylingSettingsObject">Console log settings</v-btn>
            </v-container>
        </v-card>
    </div>
</template>


<!-- 
    This is a separate setting from the theme colors, because I can only store colors in vuetify, 
    however there are other settings here like borders and paddings.
-->
<script>
    import defaultTextThemes from '../../../textThemes';
    import TextStylingService from '../../../services/TextStylingService';
    export default {
        data: function() {
            return {
                loading: false,
                resetTextStylingDialog: false,
                selectedLevelIndex: 0,
                selectedThemeIndex: 0,
                textStyling: null,
                themes: ['light', 'dark', 'eink'],
                levels: [
                    'Ignored word',
                    'New word',
                    'Known word',
                    'Level 1 word',
                    'Level 2 word',
                    'Level 3 word',
                    'Level 4 word',
                    'Level 5 word',
                    'Level 6 word',
                    'Level 7 word',
                    'Known phrase',
                    'Level 1 phrase',
                    'Level 2 phrase',
                    'Level 3 phrase',
                    'Level 4 phrase',
                    'Level 5 phrase',
                    'Level 6 phrase',
                    'Level 7 phrase',
                ],
                /*
                    On this page I used displayed level names. This object maps displayed level names to names that are used in css.
                */
                levelMapping: {
                    'Level 1 word': 'wordLevel-1',
                    'Level 2 word': 'wordLevel-2',
                    'Level 3 word': 'wordLevel-3',
                    'Level 4 word': 'wordLevel-4',
                    'Level 5 word': 'wordLevel-5',
                    'Level 6 word': 'wordLevel-6',
                    'Level 7 word': 'wordLevel-7',
                    'Known word': 'wordLevel0',
                    'Ignored word': 'wordLevel1',
                    'New word': 'wordLevel2',
                    'Selected word': 'wordLevelSelected',
                    'Known phrase': 'phraseLevel0',
                    'Level 1 phrase': 'phraseLevel-1',
                    'Level 2 phrase': 'phraseLevel-2',
                    'Level 3 phrase': 'phraseLevel-3',
                    'Level 4 phrase': 'phraseLevel-4',
                    'Level 5 phrase': 'phraseLevel-5',
                    'Level 6 phrase': 'phraseLevel-6',
                    'Level 7 phrase': 'phraseLevel-7',
                    'Selected pharse': 'phraseLevelSelected',
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
            this.loadInitialtextStylingSettingsData()
            this.updateSampleTextStyling()

            this.$emit('update', this.textStyling)
        },
        methods: {
            showResetTextStylingDialog() {
                this.resetTextStylingDialog = true;
            },
            resetDefaultTextStyling(resetType) {
                // reset only selected word/phrase level
                if (resetType === 'level') {
                    this.textStyling[this.selectedTheme][this.selectedLevel] = JSON.parse(JSON.stringify(defaultTextThemes[this.selectedTheme][this.selectedLevel]))
                    return
                }

                // reset whole theme
                this.levels.forEach((level) => {
                    this.textStyling[this.selectedTheme][level] = JSON.parse(JSON.stringify(defaultTextThemes[this.selectedTheme][level]))
                })
            },
            selectedLevelInputChanged(value) {
                this.selectedLevelIndex = this.levels.indexOf(value);
            },
            selectedThemeInputChanged(value) {
                this.selectedThemeIndex = this.themes.indexOf(value);
            },
            resetColor(colorName) {
                console.log('default color', defaultTextThemes[this.selectedTheme][this.selectedLevel][colorName])
                this.textStyling[this.selectedTheme][this.selectedLevel][colorName] = JSON.parse(JSON.stringify(defaultTextThemes[this.selectedTheme][this.selectedLevel][colorName]));
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
                    Object.assign(this.highlightedStyling, TextStylingService.getCssSettingObject(this.textStyling, this.selectedTheme, level))
                })
            

                console.log('text styling', this.highlightedStyling)
                
                this.textStyling = JSON.parse(JSON.stringify(this.textStyling))
                this.$emit('update', this.textStyling)
            },
            logTextStylingSettingsObject() {
                console.log('text styling', this.textStyling)
            },
            updateSampleTextColors() {
                this.highlightedStyling[`--interactive-text-${this.levelMapping[this.selectedLevel]}-border-color`] = this.textStyling[this.selectedTheme][this.selectedLevel].borderColor;
                this.highlightedStyling[`--interactive-text-${this.levelMapping[this.selectedLevel]}-background-color`] = this.textStyling[this.selectedTheme][this.selectedLevel].backgroundColor;
                this.highlightedStyling[`--interactive-text-${this.levelMapping[this.selectedLevel]}-color`] = this.textStyling[this.selectedTheme][this.selectedLevel].textColor;
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
