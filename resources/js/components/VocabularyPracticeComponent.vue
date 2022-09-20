<template>
    <div v-if="currentReviewIndex !== -1" id="vocabulary-practice-box">
        <div id="vocabulary-practice" v-if="!finished">
            <button id="finish-button" class="btn btn-primary" @click="finished = true">Finish</button>
            <button id="sentence-mode-button" class="btn btn-primary" @click="sentenceMode = true" v-if="!sentenceMode"><i class="fa fa-align-center"></i></button>
            <button id="word-mode-button" class="btn btn-primary" @click="sentenceMode = false" v-if="sentenceMode"><i class="fa fa-underline"></i></button>
            <div id="vocabulary-counter">{{ correctReviews + ' / ' + (reviews.length + finishedReviews.length)}}</div>
            
            <template v-if="reviews[currentReviewIndex].type == 'word'">
                <div id="vocabulary-expression" :class="{'hide-sentence': !sentenceMode}">
                    <div :class="{'vocabulary-word': true, 'highlighted': word.toLowerCase() == reviews[currentReviewIndex].word}" v-for="(word, index) in JSON.parse(reviews[currentReviewIndex].example_sentence)" :key="index" v-if="sentenceMode && word !== 'NEWLINE'">
                        {{ word }}
                    </div>
                    <div class="vocabulary-word highlighted" v-if="!sentenceMode">
                        {{ reviews[currentReviewIndex].word }}
                        <span v-if="reviews[currentReviewIndex].base_word !== ''">({{ reviews[currentReviewIndex].base_word }})</span>
                    </div>
                </div>
                <div id="vocabulary-reading" v-if="revealed">
                    {{ reviews[currentReviewIndex].reading }} <span v-if="reviews[currentReviewIndex].base_word_reading !== ''">({{ reviews[currentReviewIndex].base_word_reading }})</span>
                </div>
                <hr v-if="revealed">
                <div id="vocabulary-translation" v-if="revealed">{{ reviews[currentReviewIndex].translation }}</div>
            </template>

            <!--  -->
            <template v-if="reviews[currentReviewIndex].type == 'phrase'">
                <div id="vocabulary-expression">
                    <div :class="{'vocabulary-word': true}" v-for="(word, index) in JSON.parse(reviews[currentReviewIndex].words)" :key="index">
                        {{ word }}
                    </div>
                </div>
                <div id="vocabulary-reading" v-if="revealed">
                    {{ reviews[currentReviewIndex].reading }}
                </div>
                <hr v-if="revealed">
                <div id="vocabulary-translation" v-if="revealed">{{ reviews[currentReviewIndex].translation }}</div>
            </template>
            
            <button id="vocabulary-reveal-button" class="btn btn-primary" @click="reveal" v-if="!revealed">Reveal</button>
            <button id="vocabulary-wrong-button" class="btn btn-primary" @click="missed" v-if="revealed"><i class="fa fa-times-circle"></i></button>
            <button id="vocabulary-correct-button" class="btn btn-primary" @click="correct" v-if="revealed"><i class="fa fa-check-circle"></i></button>
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
                revealed: false,
                currentReviewIndex: -1,
                reviews: JSON.parse(this.$props._reviews),
                finishedReviews: [],
                correctReviews: 0,
                language: this.$props._language,
                readWords: 0,
                readSentences: 0,
                finished: false,
                sentenceMode: false,
                today: new moment().format('YYYY-MM-DD'),
            }
        },
        props: {
            _reviews: String,
            _language: String
        },
        mounted() {
            if (this.reviews.length) {
                this.currentReviewIndex = 0;
            } else {
                window.location.href = '/';
            }

            window.addEventListener('keyup', this.hotkey);
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

                this.$emit('keyup', event);
            },
            reveal() {
                this.revealed = true;
            },
            countReadWords() {
                if (this.reviews[this.currentReviewIndex].type == 'word') {
                    var exampleSentence = JSON.parse(this.reviews[this.currentReviewIndex].example_sentence);
                    var wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
                            '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
                            '«', '»', "'", '’', '–', 'NEWLINE'];

                    if (!this.sentenceMode) {
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

                if (!this.reviews[this.currentReviewIndex].levelLocked 
                    && this.reviews[this.currentReviewIndex].stage < 0
                    && this.reviews[this.currentReviewIndex].last_level_up !== this.today) {
                    
                    this.reviews[this.currentReviewIndex].last_level_up = this.today;
                    this.reviews[this.currentReviewIndex].stage ++;
                }

                this.reviews[this.currentReviewIndex].levelLocked = true;
                this.correctReviews ++;
                this.countReadWords();

                if (this.reviews.length == 1) {
                    this.finish();
                    return;
                }
                
                this.finishedReviews.push(this.reviews.splice(this.currentReviewIndex, 1)[0]);
                this.next();
            },
            missed() {
                this.revealed = false;
                this.reviews[this.currentReviewIndex].levelLocked = true;

                this.countReadWords();
                this.next();
            },
            next() {
                this.readSentences ++;
                
                this.currentReviewIndex = Math.floor(Math.random() * this.reviews.length);
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

                axios.post('/finish-vocabulary-practice', {
                    readWords: this.readWords,
                    reviewCount: this.readSentences,
                    changedReviews: JSON.stringify(changedReviews)
                }).then(function (response) {
                    window.location.href = '/';
                }.bind(this)).catch(function (error) {
                    console.log(error);
                }).then(function () {
                    // always
                });
            }
        },
    }
</script>

