<template>
    <v-container 
        v-if="currentReviewIndex !== -1 || finished" 
        id="review-box" 
        :class="{
            'pa-0': $vuetify.breakpoint.smAndDown, 
            'chinese-font': language == 'chinese'
        }"
    >

        <!-- Review hotkeys dialog -->
        <review-hotkey-information-dialog
            v-model="hotkeyDialog"
        ></review-hotkey-information-dialog>
        
        <!-- Review finished box -->
        <v-card 
            v-if="finished"
            outlined 
            id="finish-review-box"
            class="mt-4 mx-auto rounded-lg" 
            width="500px"
        >
            <!-- There were no cards at all -->
            <template v-if="totalReviews === 0">
                <!-- Card title -->
                <v-card-title>
                    <v-icon large color="error" class="mr-1">mdi-cards</v-icon>No cards to be reviewed.
                </v-card-title>
                
                <!-- Card content -->
                <v-card-text>
                    There are no words or phrases to be reviewed.
                </v-card-text>
            </template>

            <!-- Review finished -->
            <template v-if="totalReviews > 1">
                <!-- Card title -->
                <v-card-title>
                    <v-icon large color="success" class="mr-1">mdi-bookmark-check</v-icon>Congratulations!
                </v-card-title>
                
                <!-- Card content -->
                <v-card-text>
                    You have finished reviewing{{ formatNumber(totalReviews) }} cards. Keep up the good work, and your 
                    <span class="text-capitalize">{{ language }}</span> skills will improve steadily. Consistency is key!
                </v-card-text>
            </template>

            <!-- Card buttons -->
            <v-card-actions>
                <v-spacer />
                <v-btn rounded depressed color="primary" to="/">
                    <v-icon class="mr-1">mdi-home</v-icon>
                    Home
                </v-btn>
            </v-card-actions>
        </v-card>

        <!-- Review -->
        <div id="review" v-if="!finished">
            <!-- Progress bar -->
            <div id="review-progress-line" class="d-flex align-center">
                <v-badge
                    left
                    overlap
                    color="success"
                    icon="mdi-check"
                >
                    <div 
                        id="progress-bar-correct-counter" 
                        class="border" 
                        :style="{'border-color': $vuetify.theme.currentTheme.success}"
                    >
                        {{ correctReviews }}
                    </div>
                </v-badge>

                <v-progress-linear
                    id="review-progress-bar"
                    color="success"
                    background-color="foreground"
                    background-opacity="1"
                    height="36"
                    :value="correctReviews / totalReviews * 100"
                    class="rounded-pill border mx-6"
                >
                </v-progress-linear>
                <v-badge
                    overlap
                    color="error"
                    icon="mdi-cards"
                >
                    <div id="progress-bar-remaining-counter" class="border">{{ totalReviews - correctReviews }}</div>
                </v-badge>
            </div>
            
            <!-- Toolbar -->
            <div id="toolbar">
                <v-btn title="Fullscreen" icon class="my-2" @click="fullscreen" v-if="!settings.fullscreen"><v-icon>mdi-arrow-expand-all</v-icon></v-btn>
                <v-btn title="Exit fullscreen" icon class="my-2" @click="exitFullscreen" v-if="settings.fullscreen"><v-icon>mdi-arrow-collapse-all</v-icon></v-btn>
                <v-btn title="Toggle example sentence mode" icon class="my-2" @click="settings.sentenceMode = !settings.sentenceMode; saveSettings();"><v-icon :color="settings.sentenceMode ? 'primary' : ''">mdi-card-text</v-icon></v-btn>
                <v-btn title="Increase font size" icon class="my-2" @click="settings.fontSize ++; saveSettings();"><v-icon>mdi-magnify-plus</v-icon></v-btn>
                <v-btn title="Decrease font size" icon class="my-2" @click="settings.fontSize --; saveSettings();"><v-icon>mdi-magnify-minus</v-icon></v-btn>
                <v-btn title="Show hotkey information" icon class="my-2" @click="hotkeyDialog = !hotkeyDialog;"><v-icon>mdi-keyboard-outline</v-icon></v-btn>
            </div>

            <!-- Card -->
            <div id="review-card" 
                :class="{
                    'revealed': revealed, 
                    'back-to-deck-animation': backToDeckAnimation, 
                    'into-the-correct-deck-animation': intoTheCorrectDeckAnimation, 
                    'draw-new-card-animation': newCardAnimation
                }">
                <div id="review-card-content" class="vocab-box-area">
                    <!-- Review card front -->
                    <div id="review-card-front" class="rounded-lg border">
                        <!-- Word review -->
                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'word'">
                            <!-- Example sentence mode -->
                            <div v-show="settings.sentenceMode" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-if="reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word }} <v-icon>mdi-arrow-right-thick</v-icon> </template>
                                {{ reviews[currentReviewIndex].word }}<hr>

                                <text-block-group
                                    ref="textBlock"
                                    :theme="theme"
                                    :fullscreen="settings.fullscreen"
                                    :_text="exampleSentence"
                                    :language="language"
                                    :highlight-words="true"
                                    :plain-text-mode="false"
                                    :font-size="settings.fontSize"
                                    :line-spacing="0"
                                ></text-block-group>
                            </div>
                            
                            <!-- Single word  mode -->
                            <div class="single-word" v-if="!settings.sentenceMode" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-if="reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word }} <v-icon>mdi-arrow-right-thick</v-icon> </template>
                                {{ reviews[currentReviewIndex].word }}
                            </div>
                        </template>

                        <!-- Phrase review -->
                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'phrase'">
                            <!-- Phrase only mode -->
                            <div class="phrase-words" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-if="language == 'japanese' || language == 'chinese'">
                                    {{ JSON.parse(reviews[currentReviewIndex].words).join('') }}
                                </template>
                                <template v-else>
                                    {{ JSON.parse(reviews[currentReviewIndex].words).join(' ') }}
                                </template>

                                <!-- Example sentence mode -->
                                <hr v-if="settings.sentenceMode">
                                <div v-show="settings.sentenceMode">
                                    <text-block-group
                                        ref="textBlock"
                                        :theme="theme"
                                        :fullscreen="settings.fullscreen"
                                        :_text="exampleSentence"
                                        :language="language"
                                        :font-size="settings.fontSize"
                                    ></text-block-group>
                                </div>
                            </div>
                        </template>

                        <!-- Reveal button -->
                        <div class="review-button-box">
                            <v-btn rounded id="review-reveal-button" color="success" @click="reveal" v-if="!revealed && !newCardAnimation && !backToDeckAnimation && !intoTheCorrectDeckAnimation"><v-icon>mdi-rotate-3d-variant</v-icon> Reveal</v-btn>
                        </div>
                    </div>

                    <!-- Review card back -->
                    <div id="review-card-back" class="rounded-lg border" :style="{'background-color': backgroundColor}">
                        <!-- Word review -->
                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'word'">
                            <!-- Single word  mode -->
                            <div class="word" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-if="reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word }} <v-icon>mdi-arrow-right-thick</v-icon> </template>
                                {{ reviews[currentReviewIndex].word }}
                            </div>
                        </template>

                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'phrase'">
                            <div :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-if="language == 'japanese' || language == 'chinese'">
                                    {{ JSON.parse(reviews[currentReviewIndex].words).join('') }}
                                </template>
                                <template v-else>
                                    {{ JSON.parse(reviews[currentReviewIndex].words).join(' ') }}
                                </template>
                            </div>
                        </template>

                        <!-- Reading -->
                        <div class="reading" v-if="reviews[currentReviewIndex] !== undefined && (language == 'japanese' || language == 'chinese')" :style="{'font-size': (settings.fontSize) + 'px'}">
                            <hr>
                            <template v-if="reviews[currentReviewIndex].type == 'word' && reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word_reading }} <v-icon>mdi-arrow-right-thick</v-icon> </template>
                            {{ reviews[currentReviewIndex].reading }}
                        </div>
                        <hr>
                        
                        <!-- Phrase -->
                        <template v-if="reviews[currentReviewIndex] !== undefined">
                            <div class="phrase-words" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <span 
                                    v-for="(word, wordIndex) in exampleSentence.words" :key="wordIndex"
                                    :class="{'mr-2': word.spaceAfter}"
                                >{{ word.word }}</span>
                            </div>
                        </template>

                        <!-- Translation -->
                        <hr>
                        <div id="translation" v-if="reviews[currentReviewIndex] !== undefined" :style="{'font-size': (settings.fontSize) + 'px'}">
                            {{ reviews[currentReviewIndex].translation }}
                        </div>

                        <!-- Answer buttons -->
                        <div class="review-button-box">
                            <v-btn rounded id="review-correct-button" color="success" @click="correct" v-if="revealed">I was correct</v-btn>
                            <v-btn rounded id="review-wrong-button" color="error" @click="missed" v-if="revealed">Again</v-btn>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </v-container>
</template>

<script>
    const moment = require('moment');
    import {formatNumber} from './../../helper.js';
    export default {
        data: function() {
            return {
                theme: (this.$cookie.get('theme') === null ) ? 'light' : this.$cookie.get('theme'),
                hotkeyDialog: false,
                exampleSentence: [
                    {
                        id: -1,
                        words: [],
                        phrases: [],
                        uniqueWords: [],
                    }
                ],
                practiceMode: false,
                revealed: false,
                backToDeckAnimation: false,
                intoTheCorrectDeckAnimation: false,
                backgroundColor: this.$vuetify.theme.currentTheme.foreground,
                newCardAnimation: false,
                settings: {
                    fontSize: 20,
                    sentenceMode: false,
                    transitionDuration: this.$cookie.get('theme') === 'eink' ? 0 : 400,
                    fullscreen: false,
                },
                currentReviewIndex: -1,
                reviews: [],
                totalReviews: [],
                correctReviews: 0,
                language: '',
                readWords: 0,
                finishedReviews: -1,
                finished: false,
                today: new moment().format('YYYY-MM-DD'), // CHANGE TO SERVER SIDE
            }
        },
        props: {
        },
        mounted: function() {
            var data = {
                bookId: -1,
                chapterId: -1,
                practiceMode: this.practiceMode,
            };
            
            if (this.$route.params.bookId !== undefined) {
                data.bookId = parseInt(this.$route.params.bookId);
            }

            if (this.$route.params.chapterId !== undefined) {
                data.chapterId = parseInt(this.$route.params.chapterId);
            }

            if (this.$route.params.practiceMode !== undefined) {
                data.practiceMode = this.$route.params.practiceMode === 'true';
                this.practiceMode = this.$route.params.practiceMode === 'true';
            }


            axios.post('/reviews', data).then((response) => {
                var data = response.data;
                this.reviews = data.reviews;
                this.totalReviews = data.reviews.length;
                this.language = data.language;

                this.settings.fontSize =  parseInt(this.$cookie.get('review-font-size'));
                this.settings.sentenceMode =  this.$cookie.get('sentence-mode') == 'true';

                if (this.$cookie.get('review-font-size') === null) {
                    this.settings.fontSize =  20;
                }

                if (this.settings.sentenceMode === null) {
                    this.settings.sentenceMode =  false;
                }

                this.saveSettings();

                if (this.reviews.length) {
                    this.$nextTick(() => {
                        this.next();
                        this.$nextTick(() => {
                            document.getElementById('review-box').addEventListener('fullscreenchange', this.updateFullscreen);
                        });
                    });
                } else {
                    this.finish();
                }

                window.addEventListener('keyup', this.hotkey);
            });
        },
        beforeDestroy: function () {
            window.removeEventListener('keyup', this.hotkey);
        },
        methods: {
            hotkey (event) {
                if (!this.finished && !this.revealed && event.which == 13) {
                    this.reveal();
                }

                if (!this.finished && this.revealed && event.which == 190) {
                    this.correct();
                }

                if (!this.finished && this.revealed && event.which == 88) {
                    this.missed();
                }
            },
            fullscreen() {
                if (document.fullscreenEnabled) {
                    document.getElementById('review-box').requestFullscreen();
                    this.settings.fullscreen = true;
                }
            },
            exitFullscreen() {
                document.exitFullscreen();
                this.settings.fullscreen = false;
            },
            updateFullscreen: function() {
                this.settings.fullscreen = document.fullscreenElement !== null;
            },
            reveal() {
                if (this.intoTheCorrectDeckAnimation || this.backToDeckAnimation || this.newCardAnimation) {
                    return;
                }
                
                this.$refs.textBlock.unselectAllWords(true);
                this.revealed = true;
                this.newCardAnimation = false;
            },
            countReadWords() {
                var wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
                        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
                        '«', '»', "'", '’', '–', 'NEWLINE'];

                if (!this.settings.sentenceMode) {
                    if (this.reviews[this.currentReviewIndex].type == 'word') {
                        this.readWords ++;
                    } else {
                        this.readWords += JSON.parse(this.reviews[this.currentReviewIndex].words).length;
                    }
                } else {
                    for (var i = 0; i < this.exampleSentence.words.length; i++) {
                        if (wordsToSkip.includes(this.exampleSentence.words[i].word)) {
                            continue;
                        }

                        this.readWords ++;
                    }
                }
            },
            correct() {
                this.revealed = false;
                this.intoTheCorrectDeckAnimation = true;
                this.backToDeckAnimation = false;
                this.newCardAnimation = false;
                this.backgroundColor = this.$vuetify.theme.currentTheme.success;

                this.correctReviews ++;
                this.countReadWords();

                // update word or phrase in database
                var url = '/vocabulary/word/update';
                if (this.reviews[this.currentReviewIndex].type == 'phrase') {
                    url = '/vocabulary/phrases/update';
                }

                var saveData = {
                    id: this.reviews[this.currentReviewIndex].id
                };

                var increaseReviewAchievement = false;
                if (this.reviews[this.currentReviewIndex].relearning) {
                    saveData.relearning = false;
                    this.reviews[this.currentReviewIndex].relearning = false;
                    increaseReviewAchievement = true;
                } else {
                    saveData.stage = this.reviews[this.currentReviewIndex].stage + 1;
                    if (saveData.stage <= 0 && saveData.stage > this.reviews[this.currentReviewIndex].stage) {
                        increaseReviewAchievement = true;
                    }
                }

                if (!this.practiceMode) {
                    if (increaseReviewAchievement) {
                        axios.get('/goals/achievement/review/update');
                    }

                    axios.post(url, saveData).then(() => {
                        if (this.reviews.length == 1) {
                            this.finish();
                        } else {
                            this.reviews.splice(this.currentReviewIndex, 1)[0];
                            setTimeout(this.next, this.settings.transitionDuration);
                        }
                    });
                } else {
                    if (this.reviews.length == 1) {
                        this.finish();
                    } else {
                        this.reviews.splice(this.currentReviewIndex, 1)[0];
                        setTimeout(this.next, this.settings.transitionDuration);
                    }
                }
            },
            missed() {
                this.backToDeckAnimation = true;
                this.intoTheCorrectDeckAnimation = false;
                this.newCardAnimation = false;
                this.backgroundColor = this.$vuetify.theme.currentTheme.error;
                this.revealed = false;
                this.countReadWords();

                // update word or phrase in database
                var url = '/vocabulary/word/update';
                if (this.reviews[this.currentReviewIndex].type == 'phrase') {
                    url = '/vocabulary/phrases/update';
                }

                var saveData = {
                    id: this.reviews[this.currentReviewIndex].id,
                    savedDuringReview: true
                };
                
                if (!this.reviews[this.currentReviewIndex].relearning && !this.practiceMode) {
                    if (this.reviews[this.currentReviewIndex].stage > -7) {
                        if (this.reviews[this.currentReviewIndex].stage > -6) {
                            saveData.relearning = true;
                            this.reviews[this.currentReviewIndex].relearning = true;
                        }

                        this.reviews[this.currentReviewIndex].stage = this.reviews[this.currentReviewIndex].stage - 1;
                        saveData.stage = this.reviews[this.currentReviewIndex].stage;
                    }
                }                

                if (!this.practiceMode) {
                    axios.post(url, saveData).then(() => {
                        setTimeout(this.next, this.settings.transitionDuration);
                    });
                } else {
                    setTimeout(this.next, this.settings.transitionDuration);
                }
            },
            next() {
                this.backToDeckAnimation = false;
                this.intoTheCorrectDeckAnimation = false;
                this.newCardAnimation = true;
                this.backgroundColor = this.$vuetify.theme.currentTheme.foreground;

                setTimeout(() => {
                    this.$refs.textBlock.unselectAllWords(true);
                    this.newCardAnimation = false;
                }, this.settings.transitionDuration);

                this.finishedReviews ++;
                this.currentReviewIndex = Math.floor(Math.random() * this.reviews.length);
                this.exampleSentence = {
                    id: -1,
                    words: [],
                    phrases: [],
                    uniqueWords: [],
                };

                axios.get('/vocabulary/example-sentence/' + this.reviews[this.currentReviewIndex].type + '/' + this.reviews[this.currentReviewIndex].id).then((response) => {
                    let firstTime = (this.exampleSentence.id == -1);

                    if (response.data !== 'no example sentence') {
                        this.exampleSentence = {
                            id: 0,
                            words: response.data.words,
                            phrases: response.data.phrases,
                            uniqueWords: response.data.uniqueWords,
                        };
                    }

                    if (firstTime) {
                        this.$refs.textBlock.$forceUpdate();
                    }
                });

                // update reviewed and read words data
                axios.post('/reviews/update', {
                    readWords: this.readWords,
                }).then(() => {
                    this.readWords = 0;
                });
            },
            saveSettings() {
                if (this.settings.fontSize < 12) {
                    this.settings.fontSize = 12;
                }

                if (this.settings.fontSize > 30) {
                    this.settings.fontSize = 30;
                }

                this.$cookie.set('review-font-size', this.settings.fontSize, 3650);
                this.$cookie.set('sentence-mode', this.settings.sentenceMode, 3650);
            },
            finish() {
                this.finished = true;
            },
            formatNumber: formatNumber
        },
    }
</script>

