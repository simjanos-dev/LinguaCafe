<template>
    <div id="fullscreen-box" :class="{'fullscreen-mode': fullscreenMode}" :style="{'background-color': $vuetify.theme.currentTheme.background}">
        <!-- Hotkey information dialog -->
        <text-reader-hotkey-information-dialog
            v-model="hotkeyDialog"
        ></text-reader-hotkey-information-dialog>

        <!-- Toolbar -->
        <div id="reader-box" :style="{'max-width': maximumTextWidthData[settings.maximumTextWidth]}" v-if="chapterId !== null">
            <div id="toolbar-box">
                <div v-if="!finished && !saving" id="toolbar" :class="{'d-flex': true}" :style="{'top': toolbarTop + 'px'}">
                    <v-btn title="Fullscreen" icon @click="fullscreen" v-if="!fullscreenMode"><v-icon>mdi-arrow-expand-all</v-icon></v-btn>
                    <v-btn title="Exit fullscreen" icon @click="exitFullscreen" v-if="fullscreenMode"><v-icon>mdi-arrow-collapse-all</v-icon></v-btn>
                    <v-btn title="Text reader settings" icon @click="openDialog('settings')"><v-icon>mdi-cog</v-icon></v-btn>
                    <v-btn title="Chapters" icon @click="openDialog('chapters')"><v-icon>mdi-book-alphabet</v-icon></v-btn>
                    <v-btn title="Glossary" icon @click="openDialog('glossary')"><v-icon>mdi-translate</v-icon></v-btn>
                    <v-btn title="Increase font size" icon @click="increaseFontSize"><v-icon>mdi-magnify-plus</v-icon></v-btn>
                    <v-btn title="Decrease font size" icon @click="decreaseFontSize"><v-icon>mdi-magnify-minus</v-icon></v-btn>
                    <v-btn title="Toggle plain text mode" icon @click="settings.plainTextMode = !settings.plainTextMode; toolbarSettingChanged();"><v-icon :color="settings.plainTextMode ? 'primary' : ''">mdi-marker</v-icon></v-btn>
                    <v-btn title="Show hotkey information" icon @click="hotkeyDialog = !hotkeyDialog;"><v-icon>mdi-keyboard-outline</v-icon></v-btn>
                </div>
            </div>

            <!-- Settings -->
            <text-reader-settings
                v-if="language !== null"
                v-show="dialogs.settings"
                v-model="dialogs.settings"
                :language="language"
                ref="textReaderSettings"
                @changed="updateSettings"
            ></text-reader-settings>
            
            <!-- Chapters -->
            <text-reader-chapter-list
                :chapters="chapters"
                :current-chapter-id="chapterId"
                v-model="dialogs.chapters"
            ></text-reader-chapter-list>

            <!-- Glossary -->
            <text-reader-glossary
                :glossary="glossary"
                v-model="dialogs.glossary"
            ></text-reader-glossary>

            <!-- Text -->
            <v-card 
                :outlined="theme !== 'eink'"
                :flat="theme == 'eink'"
                v-if="!finished && !saving"
                id="reader"
                :class="{
                    'plain-text-mode': settings.plainTextMode, 
                    'vertical-text': settings.verticalText, 
                    'rounded-lg': true,
                    'ml-2': true,
                }" 
                :style="{
                    'height': $vuetify.breakpoint.mdAndUp ? 'calc(100% - 24px - 24px)' : 'calc(100% - 24px - 24px - 64px)',
                    'padding-right': settings.vocabularySidebar && vocabularySidebarFits ? '400px !important' : '0px'
                }"
            >
                <v-card-text id="reader-content" :class="{
                    'vocab-box-area': true, 
                    'px-6': $vuetify.breakpoint.smAndUp,
                    'px-3': $vuetify.breakpoint.xsOnly,
                    'pt-4': $vuetify.breakpoint.smAndUp,
                    'pt-3': $vuetify.breakpoint.xsOnly,
                }">
                    <div id="chapter-name" class="mb-4" :style="{'font-size': (settings.fontSize + 4) + 'px'}">
                        {{ chapterName }}
                    </div>

                    <text-block-group
                        v-if="text !== null"
                        ref="interactiveText"
                        :theme="theme"
                        :fullscreen="fullscreenMode"
                        :_text="text"
                        :subtitle-timestamps="subtitleTimestamps"
                        :language="language"
                        :hide-all-highlights="settings.hideAllHighlights"
                        :hide-new-word-highlights="settings.hideNewWordHighlights"
                        :plain-text-mode="settings.plainTextMode"
                        :font-size="settings.fontSize"
                        :line-spacing="settings.lineSpacing"
                        :vocab-box-scroll-into-view="settings.vocabBoxScrollIntoView"
                        :furigana-on-highlighted-words="settings.furiganaOnHighlightedWords"
                        :furigana-on-new-words="settings.furiganaOnNewWords"
                        :vocabulary-hover-box="settings.vocabularyHoverBox"
                        :vocabulary-hover-box-search="settings.vocabularyHoverBoxSearch"
                        :vocabulary-hover-box-delay="settings.vocabularyHoverBoxDelay"
                        :vocabulary-hover-box-preferred-position="settings.vocabularyHoverBoxPreferredPosition"
                        :vocabulary-sidebar="settings.vocabularySidebar"
                        :vocabulary-bottom-sheet="settings.vocabularyBottomSheet"
                        :auto-highlight-words="settings.autoHighlightWords"
                        :vocabulary-sidebar-fits="vocabularySidebarFits"
                        :hotkeys-enabled="true"
                        @increase-font-size="increaseFontSize"
                        @decrease-font-size="decreaseFontSize"
                    ></text-block-group>
                    <div :class="{
                        'd-flex': true, 
                        'mt-16': $vuetify.breakpoint.smAndUp,
                        'mb-3': $vuetify.breakpoint.xsOnly,
                    }">
                        <v-spacer></v-spacer>
                        <v-btn rounded color="primary" @click="finish()"><v-icon>mdi-text-box-check</v-icon> Finish reading</v-btn>
                    </div>
                </v-card-text>
            </v-card>&nbsp;
            
            <!-- Finish box -->
            <v-card 
                v-if="finished || saving" 
                :loading="saving"
                outlined 
                class="finished-box rounded-lg mx-auto"
                width="800px"
                background="foreground"
            >
                <!-- Title -->
                <v-card-title v-if="!saving && !finishError"><v-icon large color="success" class="mr-1">mdi-bookmark-check</v-icon>Congratulations!</v-card-title>
                <v-card-title v-if="saving">Updating data...</v-card-title>
                <v-card-text v-if="saving" height="200px"></v-card-text>
                <v-card-text v-if="!saving && finishError" height="300px">
                    <v-alert
                        class="my-3" 
                        border="left"
                        type="error"
                        v-if="finishError"
                    >
                        An error has occurred while updating your data. 
                    </v-alert>
                </v-card-text>

                <!-- Text and leveled up words list -->
                <div style="max-height: calc(100vh - 220px); overflow-y: auto;"  v-if="!saving && !finishError">
                    <v-card-text>
                        <!-- Text -->
                        You have finished reading this chapter: <b>{{ chapterName }}</b>, and you have read <b>{{ formatNumber(wordCount) }}</b> words. Keep up the good work, and your 
                        <span class="text-capitalize">{{ language }}</span> skills will improve steadily. Consistency is key!

                        <template v-if="nextChapter === -1">
                            <br><br>
                            This was the last chapter in this book.
                        </template>

                        <!-- Leveled up words -->
                        <template v-if="leveledUpWordsAndPhrases.wordsAndPhrases.length">
                            <div class="subheader mt-8">Leveled up words</div>
                            <v-data-table
                                class="no-hover"
                                :headers="[
                                    { text: 'Word', value: 'word' },
                                    { text: 'Level', value: 'stage', align: 'center', width: '180px'},
                                ]"
                                :items="leveledUpWordsAndPhrases.wordsAndPhrases"
                                :items-per-page="-1"
                                hide-default-footer
                            >
                                <!-- Stage -->
                                <template v-slot:item.word="{ item }">
                                    <template v-if="item.type === 'word'">
                                        {{ item.word }}
                                    </template>
                                    <template v-else>
                                        {{ item.words.join(languageSpaces ? ' ' : '') }}
                                    </template>
                                </template>

                                <!-- Stage -->
                                <template v-slot:item.stage="{ item }">
                                    <template v-if="item.stage === -1">
                                        <v-icon color="success" class="mr-1">mdi-check</v-icon>known
                                    </template>
                                    <template v-else>
                                        <span class="finished-stage-level rounded-pill">{{ item.stage * -1 }}</span>
                                        <v-icon class="finished-stage-arrow">mdi-arrow-right</v-icon>
                                        <span class="finished-stage-level rounded-pill">{{ (item.stage + 1) * -1 }}</span>
                                    </template>
                                </template>
                            </v-data-table>
                        </template>
                    </v-card-text>
                </div>
                
                <!-- Actions -->
                <v-card-actions>
                    <v-spacer />
                    <v-btn 
                        rounded 
                        depressed 
                        color="primary" 
                        @click="$router.push('/books/' + bookId)"
                    >
                        <v-icon class="mr-1">mdi-book-open-variant</v-icon>
                        Library
                    </v-btn>
                    <v-btn 
                        v-if="nextChapter !== -1"
                        rounded 
                        depressed 
                        color="primary" 
                        :to="'/chapters/read/' + nextChapter" 
                    >
                        <v-icon class="mr-1">mdi-page-next-outline</v-icon>
                        Next chapter
                    </v-btn>
                </v-card-actions>
            </v-card>
        </div>
    </div>
</template>

<script>
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                hotkeyDialog: false,
                text: null,
                dialogs: {
                    settings: false,
                    glossary: false,
                    chapters: false
                },
                maximumTextWidthData: ['800px', '900px', '1000px', '1200px', '1400px', '1600px', '100%'],
                toolbarTop: 68,
                theme: (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme'),
                vocabularySidebarFits: true,
                settings: {
                    hideAllHighlights: false,
                    hideNewWordHighlights: false,
                    plainTextMode: false,
                    fontSize: 20,
                    lineSpacing: 1,
                    maximumTextWidth: 3,
                    autoMoveWordsToKnown: false,
                    subtitleBlockSpacing: 1,
                    vocabBoxScrollIntoView: 'scroll-into-view',
                    verticalText: false,
                    furiganaOnHighlightedWords: false,
                    furiganaOnNewWords: false,
                    mediaControlsVisible: true,
                    vocabularySidebar: true,
                    vocabularyBottomSheet: true,
                    vocabularyHoverBox: true,
                    vocabularyHoverBoxSearch: true,
                    vocabularyHoverBoxDelay: 300,
                    vocabularyHoverBoxPreferredPosition: 'bottom',
                    autoHighlightWords: true
                },
                fullscreenMode: false,
                newlySavedWords: 0,
                learnedWords: 0,
                progressedWords: 0,
                glossary: [],
                nextChapter: -1,

                // chapter data
                type: 'text',
                subtitleTimestamps: [],
                bookName: null,
                chapterId: null,
                wordCount: 0,
                chapterName: null,
                bookId: null,
                language: null,
                languageSpaces: null,
                chapters: [],

                // finish
                finished: false,
                finishError: false,
                leveledUpWordsAndPhrases: null,
                saving: false,
            }
        },
        props: {
        },
        mounted: function () {
            window.oncontextmenu = function(event) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            };


            axios.post('/chapters/get/reader', {
                'chapterId': this.$route.params.chapterId,
            }).then((response) => {
                var data = response.data;
                this.type = data.type;

                // set subtitle timestamps
                if (this.type == 'subtitle') {
                    this.subtitleTimestamps = JSON.parse(data.subtitleTimestamps);

                    for (let i = 0; i < this.subtitleTimestamps.length; i++) {
                        for (let j = 0; j < data.words.length; j++) {
                            // find the first word of timestamp
                            if (data.words[j].sentence_index == this.subtitleTimestamps[i].sentenceIndexStart && 
                                (j == 0 || data.words[j-1].sentence_index !== data.words[j].sentence_index)) {
                                    data.words[j].subtitleIndex = i;
                            }
                        }
                    }
                }

                this.text = {
                    id: 0,
                    words: JSON.parse(JSON.stringify(data.words)),
                    phrases: JSON.parse(JSON.stringify(data.phrases)),
                    uniqueWords: JSON.parse(JSON.stringify(data.uniqueWords))
                };
                
                this.bookName = data.bookName;
                this.chapterId = data.chapterId;
                this.wordCount = data.wordCount;
                this.chapterName = data.chapterName;
                this.bookId = data.bookId;
                this.language = data.language;
                this.languageSpaces = data.languageSpaces;
                this.chapters = data.chapters;

                document.getElementById('app').addEventListener('mouseup', this.finishSelection);
                window.addEventListener('resize', this.updateToolbarPosition);
                window.addEventListener('resize', this.vocabularySidebarTest);
                window.addEventListener('scroll', this.updateToolbarPosition);
                document.getElementById('fullscreen-box').addEventListener('fullscreenchange', this.updateFullscreen);
                for (let i = 0; i < this.chapters.length; i++) {
                    if (this.chapters[i].id == this.chapterId && i < this.chapters.length - 1) {
                        this.nextChapter = this.chapters[i + 1].id;
                        break;
                    }
                }

                this.$forceUpdate();
                this.$nextTick(() => {
                    this.updateGlossary();
                });
                this.updateToolbarPosition();
                this.vocabularySidebarTest();
                this.$forceUpdate();
            });
        },
        beforeDestroy() {
            window.removeEventListener('resize', this.updateToolbarPosition);
            window.removeEventListener('resize', this.vocabularySidebarTest);
            window.removeEventListener('scroll', this.updateToolbarPosition);
        },
        // this runs after the initial data
        // was downloaded with axios
        methods: {
            vocabularySidebarTest() {
                this.vocabularySidebarFits = window.innerWidth >= 960;
            },
            fullscreen() {
                if (document.fullscreenEnabled) {
                    document.getElementById('fullscreen-box').requestFullscreen();
                    this.fullscreenMode = true;
                }
            },
            exitFullscreen() {
                document.exitFullscreen();
                this.fullscreenMode = false;
            },
            updateFullscreen: function() {
                this.fullscreenMode = document.fullscreenElement !== null;
            },
            updateToolbarPosition: function(event) {
                this.toolbarTop = 28 - document.documentElement.scrollTop;

                if (document.documentElement.scrollTop > 28 || window.innerWidth < 620) {
                    this.toolbarTop = 0;
                }
            },
            updateSettings(settings) {
                this.settings = settings;
                this.$forceUpdate();

                setTimeout(() => {
                    this.$refs.interactiveText.updateVocabBoxPosition();
                }, 200);
            },
            toolbarSettingChanged() {
                this.$refs.textReaderSettings.changeSetting('fontSize', this.settings.fontSize);
                this.$refs.textReaderSettings.changeSetting('plainTextMode', this.settings.plainTextMode, true);
            },
            openDialog(dialog) {
                if (document.fullscreenElement !== null) {
                    this.exitFullscreen();
                }

                this.$refs.interactiveText.unselectAllWords();
                this.updateGlossary();

                if (dialog == 'settings') {
                    this.dialogs.settings = true;
                }

                if (dialog == 'glossary') {
                    this.dialogs.glossary = true;
                }

                if (dialog == 'chapters') {
                    this.dialogs.chapters = true;
                }
            },
            updateGlossary() {
                this.glossary = [];

                let phrases = this.$refs.interactiveText.phrases;
                for (let i = 0; i < phrases.length; i++) {
                    if (phrases[i].stage < 0) {
                        this.glossary.push({
                            word: phrases[i].words.join(''),
                            stage: phrases[i].stage,
                            reading: phrases[i].reading,
                            base_word: '',
                            base_word_reading: '',
                            translation: phrases[i].translation,
                        });
                    }
                }

                let uniqueWords = this.$refs.interactiveText.uniqueWords;
                for (let i = 0; i < uniqueWords.length; i++) {
                    if (uniqueWords[i].stage < 0 || uniqueWords[i].stage == 2) {
                        this.glossary.push({
                            word: uniqueWords[i].word,
                            stage: uniqueWords[i].stage,
                            reading: uniqueWords[i].reading,
                            base_word: uniqueWords[i].base_word,
                            base_word_reading: uniqueWords[i].base_word_reading,
                            translation: uniqueWords[i].translation,
                        });
                    }
                }

                this.glossary.sort((a, b) => {
                    return a.stage - b.stage;
                });
            },
            increaseFontSize() {
                this.settings.fontSize ++; 
                this.toolbarSettingChanged();
            },
            decreaseFontSize() {
                this.settings.fontSize --; 
                this.toolbarSettingChanged();
            },
            finish() {
                this.leveledUpWordsAndPhrases = this.$refs.interactiveText.getLeveledUpWordsAndPhrases();
                this.saving = true;
                this.finished = true;

                axios.post('/chapters/finish', {
                    uniqueWords: JSON.stringify(this.$refs.interactiveText.uniqueWords),
                    leveledUpWords: JSON.stringify(this.leveledUpWordsAndPhrases.wordIds),
                    leveledUpPhrases: JSON.stringify(this.leveledUpWordsAndPhrases.phraseIds),
                    phrases: JSON.stringify(this.$refs.interactiveText.phrases),
                    language: this.language,
                    chapterId: this.chapterId,
                    autoMoveWordsToKnown: this.settings.autoMoveWordsToKnown
                }).then((response) => {
                    this.saving = false;
                    if (response.status === 200) {
                        this.finishError = false;
                    } else {
                        this.finishError = true;
                    }
                }).catch((error) => {
                    this.saving = false;
                    this.finishError = true;
                });
            },
            formatNumber: formatNumber
        }
    }
</script>
