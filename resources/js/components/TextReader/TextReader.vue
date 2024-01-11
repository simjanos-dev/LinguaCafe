<template>
    <div id="fullscreen-box" :class="{'fullscreen-mode': settings.fullscreen}" :style="{'background-color': $vuetify.theme.currentTheme.background}">
        <div id="reader-box" :style="{'max-width': maximumTextWidthData[settings.maximumTextWidth]}" v-if="lessonId !== null">
            <div id="toolbar-box">
                <div v-if="!finished" id="toolbar" :class="{'d-flex': true}" :style="{'top': toolbarTop + 'px'}">
                    <v-btn title="Fullscreen" icon @click="fullscreen" v-if="!settings.fullscreen"><v-icon>mdi-arrow-expand-all</v-icon></v-btn>
                    <v-btn title="Exit fullscreen" icon @click="exitFullscreen" v-if="settings.fullscreen"><v-icon>mdi-arrow-collapse-all</v-icon></v-btn>
                    <v-btn title="Text reader settings" icon @click="openDialog('settings')"><v-icon>mdi-cog</v-icon></v-btn>
                    <v-btn title="Chapters" icon @click="openDialog('chapters')"><v-icon>mdi-book-alphabet</v-icon></v-btn>
                    <v-btn title="Glossary" icon @click="openDialog('glossary')"><v-icon>mdi-translate</v-icon></v-btn>
                    <v-btn title="Increase font size" icon @click="settings.fontSize ++; saveSettings();"><v-icon>mdi-magnify-plus</v-icon></v-btn>
                    <v-btn title="Decrease font size" icon @click="settings.fontSize --; saveSettings();"><v-icon>mdi-magnify-minus</v-icon></v-btn>
                    <v-btn title="Toggle plain text mode" icon @click="settings.plainTextMode = !settings.plainTextMode; saveSettings();"><v-icon :color="settings.plainTextMode ? 'primary' : ''">mdi-marker</v-icon></v-btn>
                </div>
            </div>

            <!-- Settings -->
            <text-reader-settings
                v-if="dialogs.settings"
                v-model="dialogs.settings"
                :_hide-all-highlights="settings.hideAllHighlights"
                :_hide-new-word-highlights="settings.hideNewWordHighlights"
                :_plain-text-mode="settings.plainTextMode"
                :_japanese-text="settings.japaneseText"
                :_font-size="settings.fontSize"
                :_line-spacing="settings.lineSpacing"
                :_maximum-text-width="settings.maximumTextWidth"
                :_display-suggested-translations="settings.displaySuggestedTranslations"
                :_auto-move-words-to-known="settings.autoMoveWordsToKnown"
                :_vocab-box-scroll-into-view="settings.vocabBoxScrollIntoView"
                @changed="saveSettings"   
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
                    'japanese-text': settings.japaneseText, 
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
                        :fullscreen="settings.fullscreen"
                        :_text-blocks="textBlocks"
                        :language="language"
                        :hide-all-highlights="settings.hideAllHighlights"
                        :hide-new-word-highlights="settings.hideNewWordHighlights"
                        :plain-text-mode="settings.plainTextMode"
                        :font-size="settings.fontSize"
                        :line-spacing="settings.lineSpacing"
                        :vocab-box-scroll-into-view="settings.vocabBoxScrollIntoView"
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
                    japaneseText: false,
                    fontSize: 20,
                    lineSpacing: 1,
                    maximumTextWidth: 0,
                    displaySuggestedTranslations: false,
                    autoMoveWordsToKnown: false,
                    fullscreen: false,
                    vocabBoxScrollIntoView: 'scroll-into-view'
                },
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

                this.settings.hideAllHighlights = this.$cookie.get('hide-all-highlights') === 'true';
                this.settings.hideNewWordHighlights = this.$cookie.get('hide-new-word-highlights') === 'true';
                this.settings.plainTextMode = this.$cookie.get('plain-text-mode') === 'true';
                this.settings.japaneseText = this.$cookie.get('japanese-text') === 'true';
                this.settings.fontSize =  parseInt(this.$cookie.get('font-size'));
                this.settings.lineSpacing =  parseInt(this.$cookie.get('line-spacing'));
                this.settings.maximumTextWidth =  parseInt(this.$cookie.get('maximum-text-width'));
                this.settings.displaySuggestedTranslations = this.$cookie.get('display-suggested-translations') === 'true';
                this.settings.autoMoveWordsToKnown = this.$cookie.get('auto-move-words-to-known') === 'true';
                this.settings.vocabBoxScrollIntoView = this.$cookie.get('vocab-box-scroll-into-view');
                

                if (this.$cookie.get('hide-all-highlights') === null) {
                    this.settings.hideAllHighlights = false;
                }

                if (this.$cookie.get('hide-new-word-highlights') === null) {
                    this.settings.hideNewWordHighlights = false;
                }

                if (this.$cookie.get('plain-text-mode') === null) {
                    this.settings.plainTextMode = false;
                }

                if (this.$cookie.get('japanese-text') === null) {
                    this.settings.japaneseText =  false;
                }

                if (this.$cookie.get('font-size') === null) {
                    this.settings.fontSize =  20;
                }

                if (this.$cookie.get('line-spacing') === null) {
                    this.settings.lineSpacing =  1;
                }

                if (this.$cookie.get('maximum-text-width') === null) {
                    this.settings.maximumTextWidth =  1;
                }

                if (this.$cookie.get('display-suggested-translations') === null) {
                    this.settings.displaySuggestedTranslations =  false;
                }

                if (this.$cookie.get('auto-move-words-to-known') === null) {
                    this.settings.autoMoveWordsToKnown =  true;
                }

                if (this.$cookie.get('vocab-box-scroll-into-view') === null) {
                    this.settings.vocabBoxScrollIntoView =  'scroll-into-view';
                }

                this.saveSettings();
                this.$forceUpdate();
                this.updateGlossary();
                this.updateToolbarPosition();
            },
            fullscreen: function() {
                if (document.fullscreenEnabled) {
                    document.getElementById('fullscreen-box').requestFullscreen();
                    this.settings.fullscreen = true;
                }
            },
            exitFullscreen: function() {
                document.exitFullscreen();
                this.settings.fullscreen = false;
            },
            updateFullscreen: function() {
                this.settings.fullscreen = document.fullscreenElement !== null;
            },
            updateToolbarPosition: function(event) {
                this.toolbarTop = 28 - document.documentElement.scrollTop;

                if (document.documentElement.scrollTop > 28 || window.innerWidth < 620) {
                    this.toolbarTop = 0;
                }
            },
            saveSettings: function(newSettings = null) {
                if (newSettings !== null) {
                    this.settings = newSettings;
                }

                if (this.settings.fontSize < 12) {
                    this.settings.fontSize = 12;
                }

                if (this.settings.fontSize > 30) {
                    this.settings.fontSize = 30;
                }

                this.$cookie.set('hide-all-highlights', this.settings.hideAllHighlights, 3650);
                this.$cookie.set('hide-new-word-highlights', this.settings.hideNewWordHighlights, 3650);
                this.$cookie.set('plain-text-mode', this.settings.plainTextMode, 3650);
                this.$cookie.set('japanese-text', this.settings.japaneseText, 3650);
                this.$cookie.set('font-size', this.settings.fontSize, 3650);
                this.$cookie.set('line-spacing', this.settings.lineSpacing, 3650);
                this.$cookie.set('maximum-text-width', this.settings.maximumTextWidth, 3650);
                this.$cookie.set('display-suggested-translations', this.settings.displaySuggestedTranslations, 3650);
                this.$cookie.set('auto-move-words-to-known', this.settings.autoMoveWordsToKnown, 3650);
                this.$cookie.set('vocab-box-scroll-into-view', this.settings.vocabBoxScrollIntoView, 3650);                
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
