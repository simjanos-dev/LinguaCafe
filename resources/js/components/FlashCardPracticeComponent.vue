<template>
    <div id="flash-card-practice-box">
        <div id="flash-card-practice" v-if="!finished">
            <button id="finish-button" class="btn btn-primary" @click="finishScreen()">Finish</button>
            <div id="flash-card-counter">{{correctAnswers + '/' + flashCardCount}}</div>
            <div id="sentence">
                <template v-for="(word, index) in JSON.parse(flashCards[currentFlashCardIndex].sentence_processed)"><!--
                    --><ruby v-if="type !== 'typing' && revealed && kanjiRegex.test(word)" >
                        {{ word }}<rt>{{ flashCards[currentFlashCardIndex].reading.split(" ")[index] }}</rt>
                    </ruby><!--
                    --><ruby v-if="type == 'typing' || !revealed || !kanjiRegex.test(word)">
                        {{ word }}<rt></rt>
                    </ruby><!--
                --></template>
                <br>
            </div>
            <div id="typing-input" :style="{display: type == 'typing' && !revealed ? 'block' : 'none'}"><input type="text" ref="textInput" v-model="typingInput"></div>
            <hr v-if="revealed">
            <div id="typing-input-finalised" v-if="type == 'typing' && revealed">
                <div :class="{'typing-input-letter': true, correct: letter == flashCards[currentFlashCardIndex].reading[index]}" v-for="(letter, index) in typingInput" :key="index">{{letter}}</div>
                <div id="corrected-reading" v-if="flashCards[currentFlashCardIndex].reading != typingInput"> => {{ flashCards[currentFlashCardIndex].reading }}</div>
            </div>
            <div id="flash-card-translation" v-if="revealed">{{ flashCards[currentFlashCardIndex].translation }}</div>
            <table id="flash-card-additional-information" class="table table-sm table-bordered" v-if="type !== 'typing' && revealed && additionalInformation.length">
                <thead>
                    <tr>
                        <th>Word</th>
                        <th>Reading</th>
                        <th>Translation</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(vocab, index) in additionalInformation" :key="index">
                        <td>{{ vocab.word }}</td>
                        <td>{{ vocab.reading }}</td>
                        <td>{{ vocab.translation }}</td>
                    </tr>
                </tbody>
            </table>
            <button id="flash-card-reveal-button" class="btn btn-primary" @click="reveal" v-if="!revealed">Reveal</button>
            <button id="flash-card-wrong-button" class="btn btn-primary" @click="missed" v-if="revealed"><i class="fa fa-times-circle"></i> {{ flashCards[currentFlashCardIndex].level - 1 == 0 ? 1 : flashCards[currentFlashCardIndex].level - 1 }}</button>
            <button id="flash-card-correct-button" class="btn btn-primary" @click="correct" v-if="revealed"><i class="fa fa-check-circle"></i> {{ flashCards[currentFlashCardIndex].level + 1 == 11 ? 10 : flashCards[currentFlashCardIndex].level + 1 }}</button>
        </div>
        <div id="finished-box" v-if="finished">
            <div id="flash-card-practice-finished-text">Congratulations! You have reviewed {{ reviewCount }} flash cards!</div>
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
                        <td>Reviewed flash cards:</td>
                        <td> {{ reviewCount }} </td>
                    </tr>
                </tbody>
            </table>
            <button id="back-to-flash-cards-button" class="btn btn-primary" @click="finish()">Go to flash cards</button>
        </div>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                flashCards: JSON.parse(this.$props._flashCards),
                uniqueWords: JSON.parse(this.$props._uniqueWords),
                type: this.$props._type,
                currentFlashCardIndex: 0,
                revealed: false,
                readWords: 0,
                reviewCount: 0,
                flashCardCount: 0,
                correctAnswers: 0,
                additionalInformation: '',
                finishedFlashCards: [],
                finished: false,
                typing: true,
                typingInput: '',
                kanjiRegex: /[\u4e00-\u9faf\u3400-\u4dbf]/,
            }
        },
        props: {
            _flashCards: String,
            _uniqueWords: String,
            _type: String
        },
        mounted() {
            this.flashCardCount = this.flashCards.length;
            this.$refs.textInput.focus();
            
            window.addEventListener('keyup', this.hotkey);

            console.log(this.flashCards[this.currentFlashCardIndex].reading.split(" "));
        },
        methods: {
            hotkey (event) {
                if (!this.finished && this.typing && !this.revealed && event.which == 13) {
                    this.reveal();
                }

                if (!this.finished && this.typing && this.revealed && event.which == 190) {
                    this.correct();
                }

                if (!this.finished && this.typing && this.revealed && event.which == 88) {
                    this.missed();
                }

                this.$emit('keyup', event);
            },
            reveal() {
                this.revealed = true;

                // display translations for the words in the sentence
                var uniqueInformations = [];
                this.additionalInformation = [];

                var processedSentence = JSON.parse(this.flashCards[this.currentFlashCardIndex].sentence_processed);
                for (var i = 0; i < processedSentence.length; i++) {
                    for (var j = 0; j < this.uniqueWords.length; j++) {
                        if (this.uniqueWords[j].word == processedSentence[i].toLowerCase() && this.uniqueWords[j].translation !== '') {
                            if (uniqueInformations.indexOf(this.uniqueWords[j].word.toLowerCase()) == -1) {
                                uniqueInformations.push(this.uniqueWords[j].word.toLowerCase());
                                this.additionalInformation.push({
                                    word: this.uniqueWords[j].word,
                                    reading: this.uniqueWords[j].reading,
                                    translation: this.uniqueWords[j].translation,
                                })
                            }
                        }
                    }
                }

                // count read words
                this.reviewCount ++;
                var wordsToSkip = ['。', '、', ':', '？', '！', '＜', '＞', '：', ' ', '「', '」', '（', '）', '｛', '｝', '≪', '≫', '〈', '〉',
                        '《', '》','【', '】', '『', '』', '〔', '〕', '［', '］', '・', '?', '(', ')', ' ', ' NEWLINE ', '.', '%', '-',
                        '«', '»', "'", '’', '–', 'NEWLINE'];

                var processedSentence = JSON.parse(this.flashCards[this.currentFlashCardIndex].sentence_processed);
                for (var i = 0; i < processedSentence.length; i++) {
                    if (wordsToSkip.indexOf(processedSentence[i]) == -1) {
                        this.readWords ++;
                    }
                }
            },
            correct() {
                this.revealed = false;
                this.correctAnswers ++;

                // set new flash card level
                if (!this.flashCards[this.currentFlashCardIndex].levelChanged) {
                    this.flashCards[this.currentFlashCardIndex].levelChanged = true;
                    this.flashCards[this.currentFlashCardIndex].level ++;
                    if (this.flashCards[this.currentFlashCardIndex].level > 10) {
                        this.flashCards[this.currentFlashCardIndex].level = 10;
                    }
                }

                this.finishedFlashCards.push(this.flashCards[this.currentFlashCardIndex]);
                this.flashCards.splice(this.currentFlashCardIndex, 1);

                // show results if reviewing is finished
                if (this.flashCards.length == 0) {
                    this.finishScreen();
                    return;
                }

                // get a new random flash card
                this.currentFlashCardIndex = Math.floor(Math.random() * this.flashCards.length);
                
                // reset input
                this.typingInput = '';
                this.$nextTick(() => {
                    this.$refs.textInput.focus();
                })
                
            },
            missed() {
                this.revealed = false;

                // set new flash card level
                if (!this.flashCards[this.currentFlashCardIndex].levelChanged) {
                    this.flashCards[this.currentFlashCardIndex].levelChanged = true;
                    this.flashCards[this.currentFlashCardIndex].level --;
                    if (this.flashCards[this.currentFlashCardIndex].level < 1) {
                        this.flashCards[this.currentFlashCardIndex].level = 1;
                    }
                }

                // get a new random flash card
                var previousIndex = this.currentFlashCardIndex;
                this.currentFlashCardIndex = Math.floor(Math.random() * this.flashCards.length);
                if (this.flashCards.length > 1) {
                    while (this.currentFlashCardIndex == previousIndex) {
                        this.currentFlashCardIndex = Math.floor(Math.random() * this.flashCards.length);
                    }
                }

                // reset input
                this.typingInput = '';
                this.$nextTick(() => {
                    this.$refs.textInput.focus();
                })
            },
            finishScreen() {
                for (var i = 0; i < this.flashCards.length; i++) {
                    this.finishedFlashCards.push(this.flashCards[i]);
                }

                this.finished = true;
            },
            finish() {
                axios.post('/finish-flash-card-practice', {
                    flashCards: JSON.stringify(this.finishedFlashCards),
                    readWords: this.readWords,
                    reviewCount: this.reviewCount,
                }).then(function (response) {
                    window.location.href = '/flash-card-collections';
                }.bind(this)).catch(function (error) {
                    console.log(error);
                }).then(function () {
                    // always
                });
            }
        }
    }
</script>
