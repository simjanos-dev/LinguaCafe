<template>
    <v-container v-if="currentReviewIndex !== -1" id="review-box">
        <div id="review" v-if="!finished">
            <div id="review-progress-line">
                <div id="progress-bar-correct-counter" class="border" :style="{'background-color': $vuetify.theme.currentTheme.success}">{{ correctReviews }}</div>
                <div id="review-progress-bar">
                        <div id="review-progress-bar-correct" :style="{'width': (correctReviews / totalReviews * 100) + '%'}"></div>
                        <div id="review-progress-bar-text">{{ correctReviews }} / {{ totalReviews }}</div>
                </div>
                <div id="progress-bar-remaining-counter" class="border">{{ totalReviews - correctReviews }}</div>
            </div>

            <div id="toolbar">
                <v-btn icon class="toolbar-button pa-0" @click="finished = true"><v-icon>mdi-check-all</v-icon></v-btn>
                <v-btn-toggle class="ma-0" color="primary" group v-model="settings.fullscreen">
                    <v-btn icon class="toolbar-button pa-0" @click="fullscreen" v-if="!settings.fullscreen"><v-icon>mdi-arrow-expand-all</v-icon></v-btn>
                    <v-btn icon class="toolbar-button pa-0" :value="true" @click="exitFullscreen" v-if="settings.fullscreen"><v-icon>mdi-arrow-collapse-all</v-icon></v-btn>
                </v-btn-toggle>
                <v-btn-toggle class="ma-0" color="primary" group v-model="settings.sentenceMode">
                    <v-btn icon class="pa-0 ma-0 toolbar-button" :value="true" @click.stop="settings.sentenceMode = !settings.sentenceMode; saveSettings();"><v-icon>mdi-card-text</v-icon></v-btn>
                </v-btn-toggle>
                <v-btn icon class="toolbar-button pa-0" @click="settings.fontSize ++; saveSettings();"><v-icon>mdi-format-font-size-increase</v-icon></v-btn>
                <v-btn icon class="toolbar-button pa-0" @click="settings.fontSize --; saveSettings();"><v-icon>mdi-format-font-size-decrease</v-icon></v-btn>
            </div>

            <div id="review-card" 
                :class="{
                    'revealed': revealed, 
                    'back-to-deck-animation': backToDeckAnimation, 
                    'into-the-correct-deck-animation': intoTheCorrectDeckAnimation, 
                    'draw-new-card-animation': newCardAnimation
                }">
                <div id="review-card-content">
                    <!-- Review card front -->
                    <div id="review-card-front" class="rounded-lg border"> 
                        <!-- Word review -->
                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'word'">
                            <!-- Example sentence mode -->
                            <div class="phrase" v-if="settings.sentenceMode" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-for="(word, index) in JSON.parse(reviews[currentReviewIndex].example_sentence)">
                                    <div 
                                        :class="{
                                            'phrase-word': true, 
                                            'highlighted': word.toLowerCase() == reviews[currentReviewIndex].word
                                        }"
                                        v-if="word !== 'NEWLINE'" 
                                        :key="index"
                                    >
                                        {{ word }}
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Single word  mode -->
                            <div class="word" v-if="!settings.sentenceMode" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-if="reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word }} <v-icon>mdi-arrow-right-thick</v-icon> </template>
                                {{ reviews[currentReviewIndex].word }}
                            </div>
                        </template>

                        <!-- Phrase -->
                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'phrase'">
                            <div class="phrase" :style="{'font-size': (settings.fontSize) + 'px'}">
                                {{ JSON.parse(reviews[currentReviewIndex].words).join('') }}
                            </div>
                        </template>
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

                        <!-- Phrase -->
                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'phrase'">
                            <div class="phrase" :style="{'font-size': (settings.fontSize) + 'px'}">
                                {{ JSON.parse(reviews[currentReviewIndex].words).join('') }}
                            </div>
                        </template>

                        <!-- Reading -->
                        <div class="reading" v-if="reviews[currentReviewIndex] !== undefined" :style="{'font-size': (settings.fontSize) + 'px'}">
                            <hr>
                            <template v-if="reviews[currentReviewIndex].type == 'word' && reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word_reading }} <v-icon>mdi-arrow-right-thick</v-icon> </template>
                            {{ reviews[currentReviewIndex].reading }}
                        </div>
                        <hr>
                        <!-- Translation -->
                        <div id="translation" v-if="reviews[currentReviewIndex] !== undefined" :style="{'font-size': (settings.fontSize) + 'px'}">
                            {{ reviews[currentReviewIndex].translation }}
                        </div>
                    </div>
                </div>
            </div>

            <v-btn rounded id="review-reveal-button" color="success" @click="reveal" v-if="!revealed && !newCardAnimation && !backToDeckAnimation && !intoTheCorrectDeckAnimation"><v-icon>mdi-rotate-3d-variant</v-icon> Reveal</v-btn>
            <v-btn rounded id="review-wrong-button" color="error" @click="missed" v-if="revealed">Again</v-btn>
            <v-btn rounded id="review-correct-button" color="success" @click="correct" v-if="revealed">I was correct</v-btn>
        </div>
        <div id="finished-box" v-if="finished">
            <div id="vocabulary-practice-finished-text">Congratulations! You have reviewed {{ finishedReviews }} sentences!</div>
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
                        <td> {{ readWords }} </td>
                    </tr>
                    <tr>
                        <td>Reviewed sentences:</td>
                        <td> {{ finishedReviews }} </td>
                    </tr>
                </tbody>
            </table>
            <v-btn color="primary" @click="finish()">Close</v-btn>
        </div>
    </v-container>
</template>

<script>
    const moment = require('moment');
    export default {
        data: function() {
            return {
                //card state and animations
                language: '',
                practiceMode: false,
                revealed: false,
                backToDeckAnimation: false,
                intoTheCorrectDeckAnimation: false,
                backgroundColor: 'white',
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
                language: this.$props._language,
                readWords: 0,
                finishedReviews: -1,
                finished: false,
                today: new moment().format('YYYY-MM-DD'), // CHANGE TO SERVER SIDE
            }
        },
        props: {
            
        },
        mounted: function() {
            var data = {};
            if (this.$route.params.bookId !== undefined) {
                data.bookId = this.$route.params.bookId;
            }

            if (this.$route.params.practiceMode !== undefined) {
                data.practiceMode = this.$route.params.practiceMode;
                this.practiceMode = this.$route.params.practiceMode === 'true';
            }

            if (this.$route.params.chapterId !== undefined) {
                data.lessonId = this.$route.params.chapterId;
            }

            console.log(data);
            axios.post('/review', data).then(function (response) {
                var data = response.data;
                this.reviews = data.reviews;
                this.totalReviews = data.reviews.length;
                this.language = data.language;
                
                this.afterMounted();
            }.bind(this)).catch(function (error) {
            }).then(function () {

            });
        },
        beforeDestroy: function () {
            window.removeEventListener('keyup', this.hotkey);
        },
        methods: {
            afterMounted: function() {
                if (this.reviews.length) {
                    this.next();
                } else {
                    window.location.href = '/';
                }

                this.settings.fontSize =  parseInt(this.$cookie.get('review-font-size'));
                this.settings.sentenceMode =  this.$cookie.get('sentence-mode') == 'true';

                if (this.$cookie.get('review-font-size') === null) {
                    this.settings.fontSize =  20;
                }

                if (this.settings.sentenceMode === null) {
                    this.settings.sentenceMode =  false;
                }

                this.saveSettings();
                window.addEventListener('keyup', this.hotkey);
                this.$nextTick(() => {
                    document.getElementById('review-box').addEventListener('fullscreenchange', this.updateFullscreen);
                });
            },
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
                
                this.revealed = true;
                this.newCardAnimation = false;
            },
            countReadWords() {
                if (this.reviews[this.currentReviewIndex].type == 'word') {
                    var exampleSentence = JSON.parse(this.reviews[this.currentReviewIndex].example_sentence);
                    var wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
                            '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
                            '«', '»', "'", '’', '–', 'NEWLINE'];

                    if (!this.settings.sentenceMode) {
                        this.readWords ++;
                    } else {
                        for (var i = 0; i < exampleSentence.length; i++) {
                            if (wordsToSkip.includes(exampleSentence[i])) {
                                continue;
                            }

                            this.readWords ++;
                        }
                    }
                }

                if (this.reviews[this.currentReviewIndex].type == 'phrase') {
                    var phraseWords = JSON.parse(this.reviews[this.currentReviewIndex].words);
                    var wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
                            '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
                            '«', '»', "'", '’', '–', 'NEWLINE'];

                    for (var i = 0; i < phraseWords.length; i++) {
                        if (wordsToSkip.includes(phraseWords[i])) {
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
                var url = '/vocabulary/update';
                if (this.reviews[this.currentReviewIndex].type == 'phrase') {
                    url = '/vocabulary/phrase/update';
                }

                var saveData = {
                    id: this.reviews[this.currentReviewIndex].id
                };

                if (this.reviews[this.currentReviewIndex].relearning) {
                    saveData.relearning = false;
                    this.reviews[this.currentReviewIndex].relearning = false;
                } else {
                    saveData.stage = this.reviews[this.currentReviewIndex].stage + 1;
                }

                if (!this.practiceMode) {
                    axios.post(url, saveData).then(() => {
                        if (this.reviews.length == 1) {
                            this.finish();
                        } else {
                            this.reviews.splice(this.currentReviewIndex, 1)[0];
                            setTimeout(this.next, this.settings.transitionDuration);
                        }
                    });
                } else {
                    console.log('practicemode, update skipped');
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
                var url = '/vocabulary/update';
                if (this.reviews[this.currentReviewIndex].type == 'phrase') {
                    url = '/vocabulary/phrase/update';
                }

                var saveData = {
                    id: this.reviews[this.currentReviewIndex].id
                };
                
                if (!this.reviews[this.currentReviewIndex].relearning && !this.practiceMode) {
                    saveData.relearning = true;

                    if (this.reviews[this.currentReviewIndex].stage > -7) {
                        saveData.stage = this.reviews[this.currentReviewIndex].stage - 1;
                    } else {
                        saveData.stage = -7;
                    }

                    axios.post(url, saveData).then(() => {
                        setTimeout(this.next, this.settings.transitionDuration);
                    });
                } else {
                    console.log('practicemode, update skipped');
                    setTimeout(this.next, this.settings.transitionDuration);
                }
            },
            next() {
                this.backToDeckAnimation = false;
                this.intoTheCorrectDeckAnimation = false;
                this.newCardAnimation = true;
                this.backgroundColor = 'white';

                setTimeout(() => {
                    this.newCardAnimation = false;
                }, this.settings.transitionDuration);

                this.finishedReviews ++;
                this.currentReviewIndex = Math.floor(Math.random() * this.reviews.length);
                
                // update reviewed and read words data
                console.log(this.readWords, this.finishedReviews);
                axios.post('/review/update', {
                    readWords: this.readWords,
                    reviewCount: this.finishedReviews,
                }).then(() => {
                    this.readWords = 0;
                    this.finishedReviews = 0;
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
                this.$router.push('/');
            }
        },
    }
</script>

