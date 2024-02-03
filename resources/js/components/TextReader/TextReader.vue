<template>
    <div id="fullscreen-box" :class="{'fullscreen-mode': fullscreenMode}" :style="{'background-color': $vuetify.theme.currentTheme.background}">
        <!-- Hotkey information dialog -->
        <text-reader-hotkey-information-dialog
            v-model="hotkeyDialog"
        ></text-reader-hotkey-information-dialog>

        <!-- Toolbar -->
        <div id="reader-box" :style="{'max-width': maximumTextWidthData[settings.maximumTextWidth]}" v-if="lessonId !== null">
            <div id="toolbar-box">
                <div v-if="!finished" id="toolbar" :class="{'d-flex': true}" :style="{'top': toolbarTop + 'px'}">
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
                v-show="dialogs.settings"
                v-model="dialogs.settings"
                ref="textReaderSettings"
                @changed="updateSettings"
            ></text-reader-settings>
            
            <!-- Chapters -->
            <text-reader-chapter-list
                :chapters="lessons"
                :current-chapter-id="lessonId"
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
                v-if="!finished"
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
                        {{ lessonName }}
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
                        :vocabulary-sidebar="settings.vocabularySidebar"
                        :auto-highlight-words="settings.autoHighlightWords"
                        :vocabulary-sidebar-fits="vocabularySidebarFits"
                        :hotkeys-enabled="true"
                        @increase-font-size="increaseFontSize"
                        @decrease-font-size="decreaseFontSize"
                    ></text-block-group>    
                    <v-alert
                        class="my-3" 
                        border="left"
                        type="error"
                        v-if="finishError"
                        >
                        Something went wrong. Please try again.
                    </v-alert>
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
                v-if="finished" 
                outlined 
                id="finished-box"
                class="rounded-lg mx-auto"
                width="500px"
            >
                <!-- Title -->
                <v-card-title><v-icon large color="success" class="mr-1">mdi-bookmark-check</v-icon>Congratulations!</v-card-title>
                
                <!-- Text -->
                <v-card-text>
                    You have finished reading this chapter: <b>{{ lessonName }}</b>, and you have read <b>{{ formatNumber(wordCount) }}</b> words. Keep up the good work, and your 
                    <span class="text-capitalize">{{ language }}</span> skills will improve steadily. Consistency is key!

                    <template v-if="nextLesson === -1">
                        <br><br>
                        This was the last chapter in this book.
                    </template>
                </v-card-text>
                
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
                        v-if="nextLesson !== -1"
                        rounded 
                        depressed 
                        color="primary" 
                        :to="'/chapters/read/' + nextLesson" 
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
                    vocabularyHoverBox: true,
                    autoHighlightWords: true
                },
                fullscreenMode: false,
                finished: false,
                finishError: false,
                newlySavedWords: 0,
                learnedWords: 0,
                progressedWords: 0,
                glossary: [],
                nextLesson: -1,

                // lesson data
                type: 'text',
                subtitleTimestamps: [],
                bookName: null,
                lessonId: null,
                wordCount: 0,
                lessonName: null,
                bookId: null,
                language: null,
                lessons: [],
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


            axios.post('/chapter/get/reader', {
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
                this.lessonId = data.lessonId;
                this.wordCount = data.wordCount;
                this.lessonName = data.lessonName;
                this.bookId = data.bookId;
                this.language = data.language;
                this.lessons = data.lessons;

                document.getElementById('app').addEventListener('mouseup', this.finishSelection);
                window.addEventListener('resize', this.updateToolbarPosition);
                window.addEventListener('resize', this.vocabularySidebarTest);
                window.addEventListener('scroll', this.updateToolbarPosition);
                document.getElementById('fullscreen-box').addEventListener('fullscreenchange', this.updateFullscreen);
                for (let i = 0; i < this.lessons.length; i++) {
                    if (this.lessons[i].id == this.lessonId && i < this.lessons.length - 1) {
                        this.nextLesson = this.lessons[i + 1].id;
                        break;
                    }
                }

                this.$forceUpdate();
                this.updateGlossary();
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
            fullscreen: function() {
                if (document.fullscreenEnabled) {
                    document.getElementById('fullscreen-box').requestFullscreen();
                    this.fullscreenMode = true;
                }
            },
            exitFullscreen: function() {
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
            openDialog: function(dialog) {
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
            updateGlossary: function() {
                this.glossary = [];
                
                for (let i = 0; i < this.text.phrases.length; i++) {
                    if (this.text.phrases[i].stage < 0) {
                        this.glossary.push({
                            word: this.text.phrases[i].words.join(''),
                            stage: this.text.phrases[i].stage,
                            reading: this.text.phrases[i].reading,
                            base_word: '',
                            base_word_reading: '',
                            translation: this.text.phrases[i].translation,
                        });
                    }
                }

                for (let i = 0; i < this.text.uniqueWords.length; i++) {
                    if (this.text.uniqueWords[i].stage < 0 || this.text.uniqueWords[i].stage == 2) {
                        this.glossary.push({
                            word: this.text.uniqueWords[i].word,
                            stage: this.text.uniqueWords[i].stage,
                            reading: this.text.uniqueWords[i].reading,
                            base_word: this.text.uniqueWords[i].base_word,
                            base_word_reading: this.text.uniqueWords[i].base_word_reading,
                            translation: this.text.uniqueWords[i].translation,
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
            finish: function() {
                axios.post('/chapter/finish', {
                    uniqueWords: JSON.stringify(this.text.uniqueWords),
                    sentences: JSON.stringify(this.text.sentences),
                    language: this.language,
                    lessonId: this.lessonId,
                    autoMoveWordsToKnown: this.settings.autoMoveWordsToKnown
                }).then((response) => {
                    if (response.data == 'success') {

                        this.finished = true;
                    } else {
                        this.finishError = true;
                    }
                }).catch(function (error) {
                    console.log(error);
                });
            },
            formatNumber: formatNumber
        }
    }
</script>
