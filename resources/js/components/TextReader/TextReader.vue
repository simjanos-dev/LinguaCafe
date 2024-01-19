<template>
    <div id="fullscreen-box" :class="{'fullscreen-mode': fullscreenMode}" :style="{'background-color': $vuetify.theme.currentTheme.background}">
        <div id="reader-box" :style="{'max-width': maximumTextWidthData[settings.maximumTextWidth]}" v-if="lessonId !== null">
            <div id="toolbar-box">
                <div v-if="!finished" id="toolbar" :class="{'d-flex': true}" :style="{'top': toolbarTop + 'px'}">
                    <v-btn title="Fullscreen" icon @click="fullscreen" v-if="!fullscreenMode"><v-icon>mdi-arrow-expand-all</v-icon></v-btn>
                    <v-btn title="Exit fullscreen" icon @click="exitFullscreen" v-if="fullscreenMode"><v-icon>mdi-arrow-collapse-all</v-icon></v-btn>
                    <v-btn title="Text reader settings" icon @click="openDialog('settings')"><v-icon>mdi-cog</v-icon></v-btn>
                    <v-btn title="Chapters" icon @click="openDialog('chapters')"><v-icon>mdi-book-alphabet</v-icon></v-btn>
                    <v-btn title="Glossary" icon @click="openDialog('glossary')"><v-icon>mdi-translate</v-icon></v-btn>
                    <v-btn title="Increase font size" icon @click="settings.fontSize ++; toolbarSettingChanged();"><v-icon>mdi-magnify-plus</v-icon></v-btn>
                    <v-btn title="Decrease font size" icon @click="settings.fontSize --; toolbarSettingChanged();"><v-icon>mdi-magnify-minus</v-icon></v-btn>
                    <v-btn title="Toggle plain text mode" icon @click="settings.plainTextMode = !settings.plainTextMode; toolbarSettingChanged();"><v-icon :color="settings.plainTextMode ? 'primary' : ''">mdi-marker</v-icon></v-btn>
                </div>
            </div>

            <!-- Settings -->
            <text-reader-settings
                v-show="dialogs.settings"
                v-model="dialogs.settings"
                ref="textReaderSettings"
                :enabledSettings="[
                    'hideAllHighlights', 
                    'hideNewWordHighlights', 
                    'plainTextMode', 
                    'verticalText', 
                    'fontSize', 
                    'lineSpacing', 
                    'maximumTextWidth', 
                    'autoMoveWordsToKnown', 
                    'vocabBoxScrollIntoView', 
                    'furiganaOnHighlightedWords', 
                    'furiganaOnNewWords', 
                ]"
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
                    'vocab-box-area': true, 
                    'plain-text-mode': settings.plainTextMode, 
                    'vertical-text': settings.verticalText, 
                    'rounded-lg': true,
                    'ml-2': true,
                    'pa-4': $vuetify.breakpoint.smAndUp,
                    'pa-3': $vuetify.breakpoint.xsOnly
                }" 
            >
                <v-card-text>
                    <div id="chapter-name" class="mb-4" :style="{'font-size': (settings.fontSize + 4) + 'px'}">
                        {{ lessonName }}
                    </div>

                    <text-block-group
                        v-if="textBlocks.length"
                        ref="textBlock"
                        :theme="theme"
                        :fullscreen="fullscreenMode"
                        :_text-blocks="textBlocks"
                        :language="language"
                        :hide-all-highlights="settings.hideAllHighlights"
                        :hide-new-word-highlights="settings.hideNewWordHighlights"
                        :plain-text-mode="settings.plainTextMode"
                        :font-size="settings.fontSize"
                        :line-spacing="settings.lineSpacing"
                        :vocab-box-scroll-into-view="settings.vocabBoxScrollIntoView"
                        :furigana-on-highlighted-words="settings.furiganaOnHighlightedWords"
                        :furigana-on-new-words="settings.furiganaOnNewWords"
                    ></text-block-group>    
                </v-card-text>
                <v-alert
                    class="my-3" 
                    border="left"
                    type="error"
                    v-if="finishError"
                    >
                    Something went wrong. Please try again.
                </v-alert>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn rounded color="primary" @click="finish()"><v-icon>mdi-text-box-check</v-icon> Finish reading</v-btn>
                </v-card-actions>
            </v-card>&nbsp;
            
            <!-- Finish -->
            <div v-if="finished" id="finished-box">
                <div id="lesson-finished-text">Congratulations! You have finished {{ lessonName }}!</div>

                <table id="finished-stats" class="table-sm table-bordered">
                    <thead>
                        <tr>
                            <th scope="col">Word type</th>
                            <th scope="col">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Read words:</td>
                            <td> {{ wordCount }} </td>
                        </tr>
                        <tr>
                            <td>Newly saved words:</td>
                            <td> {{ newlySavedWords }} </td>
                        </tr>
                        <tr>
                            <td>Learned words:</td>
                            <td> {{ learnedWords }} </td>
                        </tr>
                        <tr>
                            <td>Progressed words:</td>
                            <td> {{ progressedWords }} </td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-center">
                    <v-btn depressed :small="$vuetify.breakpoint.xsOnly" color="primary" :to="'/chapters/' + bookId">Go to lessons</v-btn>
                    <v-btn depressed :small="$vuetify.breakpoint.xsOnly" color="primary" :to="'/chapters/read/' + nextLesson" v-if="nextLesson !== -1">Go to next lesson</v-btn>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                textBlocks: [],
                dialogs: {
                    settings: false,
                    glossary: false,
                    chapters: false
                },
                maximumTextWidthData: ['800px', '900px', '1000px', '1200px', '1400px', '1600px', '100%'],
                toolbarTop: 68,
                theme: (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme'),
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
                    mediaControlsVisible: true
                },
                fullscreenMode: false,
                finished: false,
                finishError: false,
                newlySavedWords: 0,
                learnedWords: 0,
                progressedWords: 0,
                glossary: [],
                nextLesson: -1,

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
                this.textBlocks.push({
                    id: 0,
                    words: JSON.parse(JSON.stringify(data.words)),
                    phrases: JSON.parse(JSON.stringify(data.phrases)),
                    uniqueWords: JSON.parse(JSON.stringify(data.uniqueWords))
                });

                this.bookName = data.bookName;
                this.lessonId = data.lessonId;
                this.wordCount = data.wordCount;
                this.lessonName = data.lessonName;
                this.bookId = data.bookId;
                this.language = data.language;
                this.lessons = data.lessons;

                this.afterMounted();
            });
        },
        // this runs after the initial data
        // was downloaded with axios
        methods: {
            afterMounted: function() {
                document.getElementById('app').addEventListener('mouseup', this.finishSelection);
                window.addEventListener('resize', this.updateToolbarPosition);
                window.addEventListener('scroll', this.updateToolbarPosition);
                document.getElementById('fullscreen-box').addEventListener('fullscreenchange', this.updateFullscreen);
                for (let i = 0; i < this.lessons.length; i++) {
                    if (this.lessons[i].id == this.lessonId && i < this.lessons.length - 1 && this.lessons[i + 1].read_count) {
                        this.nextLesson = this.lessons[i + 1].id;
                        break;
                    }
                }

                this.$forceUpdate();
                this.updateGlossary();
                this.updateToolbarPosition();
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
            },
            toolbarSettingChanged() {
                this.$refs.textReaderSettings.changeSetting('fontSize', this.settings.fontSize);
                this.$refs.textReaderSettings.changeSetting('plainTextMode', this.settings.plainTextMode, true);
            },
            openDialog: function(dialog) {
                if (document.fullscreenElement !== null) {
                    this.exitFullscreen();
                }

                this.$refs.textBlock.unselectAllWords();
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
                for (let j = 0; j < this.textBlocks.length; j++) {
                    for (let i = 0; i < this.textBlocks[j].phrases.length; i++) {
                        if (this.textBlocks[j].phrases[i].stage < 0) {
                            this.glossary.push({
                                word: this.textBlocks[j].phrases[i].words.join(''),
                                stage: this.textBlocks[j].phrases[i].stage,
                                reading: this.textBlocks[j].phrases[i].reading,
                                base_word: '',
                                base_word_reading: '',
                                translation: this.textBlocks[j].phrases[i].translation,
                            });
                        }
                    }

                    for (let i = 0; i < this.textBlocks[j].uniqueWords.length; i++) {
                        if (this.textBlocks[j].uniqueWords[i].stage < 0 || this.textBlocks[j].uniqueWords[i].stage == 2) {
                            this.glossary.push({
                                word: this.textBlocks[j].uniqueWords[i].word,
                                stage: this.textBlocks[j].uniqueWords[i].stage,
                                reading: this.textBlocks[j].uniqueWords[i].reading,
                                base_word: this.textBlocks[j].uniqueWords[i].base_word,
                                base_word_reading: this.textBlocks[j].uniqueWords[i].base_word_reading,
                                translation: this.textBlocks[j].uniqueWords[i].translation,
                            });
                        }
                    }
                }

                this.glossary.sort((a, b) => {
                    return a.stage - b.stage;
                });
            },
            finish: function() {
                axios.post('/chapter/finish', {
                    uniqueWords: JSON.stringify(this.textBlocks[0].uniqueWords),
                    sentences: JSON.stringify(this.textBlocks[0].sentences),
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
            }
        }
    }
</script>
