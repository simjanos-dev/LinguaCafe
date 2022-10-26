<template>
    <div v-if="currentReviewIndex !== -1" id="review-box">
        <div id="review" v-if="!finished">
            <div id="review-progress-line">
                <div id="progress-bar-correct-counter">{{ correctReviews }}</div>
                <div id="review-progress-bar">
                        <div id="review-progress-bar-correct" :style="{'width': (correctReviews / totalReviews * 100) + '%'}"></div>
                        <div id="review-progress-bar-seen" :style="{'width': (seen / totalReviews * 100) + '%'}"></div>
                        <div id="review-progress-bar-text">{{ correctReviews }} / {{ totalReviews }}</div>
                </div>
                <div id="progress-bar-remaining-counter">{{ reviews.length + finishedReviews.length - correctReviews }}</div>
            </div>

            <div id="toolbar">
                <button class="toolbar-button" @click="finished = true"><i class="fa fa-check"></i></button>
                <button class="toolbar-button" @click="fullscreen" v-if="!settings.fullscreen"><i class="fa fa-expand"></i></button>
                <button class="toolbar-button" @click="exitFullscreen" v-if="settings.fullscreen"><i class="fa fa-compress"></i></button>
                <button class="toolbar-button" @click="settings.sentenceMode = true; saveSettings();" v-if="!settings.sentenceMode"><i class="fa fa-align-center"></i></button>
                <button class="toolbar-button" @click="settings.sentenceMode = false; saveSettings();" v-if="settings.sentenceMode"><i class="fa fa-underline"></i></button>
                <button class="toolbar-button" @click="settings.fontSize ++; saveSettings();"><i class="fa fa-search-plus"></i></button>
                <button class="toolbar-button" @click="settings.fontSize --; saveSettings();"><i class="fa fa-search-minus"></i></button>
            </div>

            <div id="review-card" :class="{'revealed': revealed, 'back-to-deck-animation': backToDeckAnimation, 'into-the-correct-deck-animation': intoTheCorrectDeckAnimation, 'draw-new-card-animation': newCardAnimation}">
                <div id="review-card-content">
                    <!-- Review card front -->
                    <div id="review-card-front">
                        
                        <!-- Word review -->
                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'word'">
                            <!-- Example sentence mode -->
                            <div class="phrase" v-if="settings.sentenceMode" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-for="(word, index) in JSON.parse(reviews[currentReviewIndex].example_sentence)">
                                    <div :class="{'phrase-word': true, 'highlighted': word.toLowerCase() == reviews[currentReviewIndex].word}" v-if="word !== 'NEWLINE'" :key="index">
                                        {{ word }}
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Single word  mode -->
                            <div class="word" v-if="!settings.sentenceMode" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-if="reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word }} <i class="fas fa-long-arrow-alt-right"></i> </template>
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
                    <div id="review-card-back">
                        <!-- Word review -->
                        <template v-if="reviews[currentReviewIndex] !== undefined && reviews[currentReviewIndex].type == 'word'">
                            <!-- Single word  mode -->
                            <div class="word" :style="{'font-size': (settings.fontSize) + 'px'}">
                                <template v-if="reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word }} <i class="fas fa-long-arrow-alt-right"></i> </template>
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
                            <template v-if="reviews[currentReviewIndex].type == 'word' && reviews[currentReviewIndex].base_word !== ''">{{ reviews[currentReviewIndex].base_word_reading }} <i class="fas fa-long-arrow-alt-right"></i> </template>
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

            <button id="review-reveal-button" class="btn btn-green" @click="reveal" v-if="!revealed && !newCardAnimation && !backToDeckAnimation && !intoTheCorrectDeckAnimation"><i class="fa fa-sync"></i> Reveal</button>
            <button id="review-wrong-button" class="btn btn-orange" @click="missed" v-if="revealed"><i class="fa fa-times"></i> Again</button>
            <button id="review-correct-button" class="btn btn-green" @click="correct" v-if="revealed"><i class="fa fa-check"></i> I was correct</button>
        </div>
        <div id="finished-box" v-if="finished">
            <div id="vocabulary-practice-finished-text">Congratulations! You have reviewed {{ readSentences }} sentences!</div>
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
                        <td> {{ readSentences }} </td>
                    </tr>
                </tbody>
            </table>
            <button id="close-button" class="btn btn-primary" @click="finish()">Close</button>
        </div>
    </div>
</template>

<script>
    const moment = require('moment');
    export default {
        data: function() {
            return {
                //card state and animations
                language: '',
                revealed: false,
                backToDeckAnimation: false,
                intoTheCorrectDeckAnimation: false,
                newCardAnimation: false,
                settings: {
                    fontSize: 20,
                    sentenceMode: false,
                    transitionDuration: this.$cookie.get('ebook-reader-mode') === null ? 400 : 0,
                    fullscreen: false,
                },
                currentReviewIndex: -1,
                reviews: [],
                totalReviews: [],
                seen: 0,
                finishedReviews: [],
                correctReviews: 0,
                language: this.$props._language,
                readWords: 0,
                readSentences: 0,
                finished: false,
                today: new moment().format('YYYY-MM-DD'), // CHANGE TO SERVER SIDE
            }
        },
        props: {
            
        },
        mounted() {
            var data = {};
            if (this.$route.params.bookId !== undefined) {
                data.courseId = this.$route.params.bookId;
            }

            if (this.$route.params.chapterId !== undefined) {
                data.lessonId = this.$route.params.chapterId;
            }

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
        methods: {
            afterMounted: function() {
                if (this.reviews.length) {
                    this.currentReviewIndex = 0;
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

                this.$emit('keyup', event);
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

                if (!this.reviews[this.currentReviewIndex].levelLocked 
                    && this.reviews[this.currentReviewIndex].stage < 0
                    && this.reviews[this.currentReviewIndex].last_level_up !== this.today) {
                    
                    this.reviews[this.currentReviewIndex].last_level_up = this.today;
                    this.reviews[this.currentReviewIndex].stage ++;
                }

                if (this.reviews[this.currentReviewIndex].levelLocked) {
                    this.seen --;
                }

                this.reviews[this.currentReviewIndex].levelLocked = true;
                this.correctReviews ++;
                this.countReadWords();

                if (this.reviews.length == 1) {
                    this.finish();
                    return;
                }
                
                this.finishedReviews.push(this.reviews.splice(this.currentReviewIndex, 1)[0]);
                setTimeout(this.next, this.settings.transitionDuration);
            },
            missed() {
                this.backToDeckAnimation = true;
                this.intoTheCorrectDeckAnimation = false;
                this.newCardAnimation = false;

                this.revealed = false;
                if (!this.reviews[this.currentReviewIndex].levelLocked) {
                    this.reviews[this.currentReviewIndex].levelLocked = true;
                    this.seen ++;
                }

                

                this.countReadWords();
                setTimeout(this.next, this.settings.transitionDuration);
            },
            next() {
                this.backToDeckAnimation = false;
                this.intoTheCorrectDeckAnimation = false;
                this.newCardAnimation = true;

                setTimeout(() => {
                    this.newCardAnimation = false;
                }, this.settings.transitionDuration);

                this.readSentences ++;
                this.currentReviewIndex = Math.floor(Math.random() * this.reviews.length);
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
                var changedReviews = [];
                for (var i = 0; i < this.finishedReviews.length; i++) {
                    if (this.finishedReviews[i].levelLocked) {
                        changedReviews.push({
                            id: this.finishedReviews[i].id, 
                            type: this.finishedReviews[i].type,
                            stage: this.finishedReviews[i].stage,
                            last_level_up: this.finishedReviews[i].last_level_up
                        });
                    }
                }

                for (var i = 0; i < this.reviews.length; i++) {
                    if (this.reviews[i].levelLocked) {
                        changedReviews.push({
                            id: this.reviews[i].id, 
                            type: this.reviews[i].type,
                            stage: this.reviews[i].stage,
                            last_level_up: this.reviews[i].last_level_up
                        });
                    }
                }

                axios.post('/review/finish', {
                    readWords: this.readWords,
                    reviewCount: this.readSentences,
                    changedReviews: JSON.stringify(changedReviews)
                }).then(function (response) {
                    this.$router.push('/');
                }.bind(this)).catch(function (error) {
                    console.log(error);
                }).then(function () {
                    // always
                });
            }
        },
    }
</script>

