<template>
    <div class="w-100 overflow-x-hidden" :textblock="$props.textBlockId" @mousemove="removePhraseHover">
        <template v-for="(word, wordIndex) in words"><!--
            --><div 
                :key="wordIndex"
                v-if="word.word !== 'NEWLINE' && language == 'japanese'" 
                :wordindex="wordIndex" 
                :stage="word.stage" 
                :phrasestage="word.phraseStage" 
                :class="{
                    'no-highlight': !highlightWords,
                    'plain-text-mode': plainTextMode,
                    'word': true,
                    'highlighted': word.selected || word.hover,
                    'phrase': word.phraseIndexes.length > 0, 
                    'phrase-start': word.phraseStart, 
                    'phrase-end': word.phraseEnd
                }"
                :style="{
                    'font-size': fontSize + 'px', 
                    'margin-bottom': (lineSpacing * 4) + 'px'
                }" 
                
                
                @pointerenter="hoverPhraseSelection(wordIndex);"
                @touchstart="startSelectionTouch($event, wordIndex)" 
                @mousedown.stop="startSelection($event, wordIndex)" 
                @touchmove="updateSelectionTouch($event, wordIndex);" 
                @mousemove.stop="updateSelectionMouse($event, wordIndex);" 
                @touchend.stop="finishSelection($event)"
                @mouseup.stop="finishSelection($event)"
                @mouseleave=";"
            >{{ word.word }}</div><!--
            --><br v-if="word.word == 'NEWLINE'"><!--
        --></template>
    </div>
</template>

<script>
    export default {    
        data: function() {
            return {
                words: this.$props._words,
                phrases: this.$props._phrases,
                uniqueWords: this.$props._uniqueWords,
                touchTimer: null,
                selectionOngoing: false,
                selection: [],
                ongoingSelection: [],
                selectedPhrase: -1,
                ongoingSelectionStartingWordIndex: -1,

                vocabBox: {
                    tab: 0,
                    closed: true,
                    selectedStageButton: 0,
                    width: window.innerWidth > 440 ? 400 : window.innerWidth - 20,
                    position: {
                        left: 0,
                        top: 0
                    },
                    searchField: '',
                    searchResults: [],
                    translationText: '',
                    translationList: [],
                    reading: '',
                    base_word: '',
                    base_word_reading: '',
                },
            }
        },
        props: {
            textBlockId: Number,
            _words: Array,
            _phrases: Array,
            _uniqueWords: Array,
            language: String,
            highlightWords: Boolean,
            plainTextMode: Boolean,
            fontSize: Number,
            lineSpacing: Number
        },
        watch: { 
            _words: {
                handler: function(newVal, oldVal) {
                    this.words = newVal;
                },
                flush: 'post'
            },

            _phrases: {
                handler: function(newVal, oldVal) {
                    this.phrases = newVal;
                },
                flush: 'post'
            },
            _uniqueWords: {
                handler: function(newVal, oldVal) {
                    this.uniqueWords = newVal;
                    this.updatePhraseBorders();
                },
                flush: 'post'
            }
        },
        mounted() {
            this.updatePhraseBorders();
        },
    methods: {
            hoverPhraseSelection: function(wordIndex) {
                this.removePhraseHover();
                var phraseIndexes = this.words[wordIndex].phraseIndexes;
                if (!phraseIndexes.length) {
                    return;
                }

                // find the first word of the phrase
                var currentWordIndex = wordIndex;
                while (currentWordIndex > 0 && (this.words[currentWordIndex - 1].word == 'NEWLINE' || this.words[currentWordIndex - 1].phraseIndexes.some(el => phraseIndexes.includes(el)))) {
                    currentWordIndex--;
                }

                // highlight the phrase
                
                do {
                    this.words[currentWordIndex].hover = true;
                    currentWordIndex ++;
                } while(currentWordIndex < this.words.length && (this.words[currentWordIndex].word == 'NEWLINE' || this.words[currentWordIndex].phraseIndexes.some(el => phraseIndexes.includes(el))));
            },
            removePhraseHover: function() {
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].hover = false;
                }
            },
            selectPhraseInstanceByWord: function(wordIndex, phraseIndex) {
                var currentWordIndex = wordIndex;
                var newSelection = [];

                // find the first word of the phrase
                while (currentWordIndex > 0 && (this.words[currentWordIndex - 1].word == 'NEWLINE' || this.words[currentWordIndex - 1].phraseIndexes.includes(phraseIndex))) {
                    currentWordIndex --;
                }

                // select the phrasew
                do {
                    if (this.words[currentWordIndex].word !== 'NEWLINE') {
                        newSelection.push({
                            word: this.words[currentWordIndex].word,
                            reading: this.uniqueWords[this.getUniqueWordIndex(this.words[currentWordIndex].word.toLowerCase())].reading,
                            sentence_index: this.words[currentWordIndex].sentence_index,
                            wordIndex: currentWordIndex,
                            uniqueWordIndex: this.getUniqueWordIndex(this.words[currentWordIndex].word.toLowerCase()),
                        });
                    }

                    currentWordIndex ++;
                } while(currentWordIndex < this.words.length && (this.words[currentWordIndex].word == 'NEWLINE' || this.words[currentWordIndex].phraseIndexes.includes(phraseIndex)));
                
                this.ongoingSelection = newSelection;
            },
            startSelectionTouch: function(event, wordIndex) {
                this.touchTimer = setTimeout(() => {
                    this.startSelection(event, wordIndex);
                }, 500);
            },
            startSelection: function(event, wordIndex) {
                this.$emit('unselectAllWords', true);
                this.touchTimer = null;
                if (event == undefined) {
                    return;
                }

                if (event.cancelable) {
                    event.preventDefault();
                }

                this.selectionOngoing = true;

                if (this.ongoingSelection.length == 1 && this.ongoingSelection[0].wordIndex == wordIndex) {
                    return;
                }

                if (this.$props.plainTextMode) {
                    return;
                }

                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                }
                
                // set selected word          
                var selectedWord = {
                    word: event.srcElement.outerText,
                    wordIndex: wordIndex,
                    uniqueWordIndex: this.getUniqueWordIndex(event.srcElement.outerText.toLowerCase()),
                    reading: this.uniqueWords[this.getUniqueWordIndex(this.words[wordIndex].word.toLowerCase())].reading,
                    sentence_index: this.words[wordIndex].sentence_index,
                    position: event.target.getBoundingClientRect(),
                };
                
                this.ongoingSelection = [selectedWord];
                this.words[wordIndex].selected = true;
                this.ongoingSelectionStartingWordIndex = wordIndex;
                this.updateSelectedWordLookupCount(selectedWord.word, selectedWord.uniqueWordIndex);
            },
            updateSelectionMouse: function(event, wordIndex) {
                if (!this.ongoingSelection.length || event == undefined || event.buttons !== 1 || this.touchTimer) {
                    return;
                }

                this.updateSelection(wordIndex);
            },
            updateSelectionTouch: function(event, wordIndex) {
                if (this.touchTimer) {
                    clearTimeout(this.touchTimer);
                    this.touchTimer = null;
                    return;
                } else {
                    if (event.cancelable) {
                        event.preventDefault();
                    }
                }
                
                var touch = event.changedTouches[0];
                var element = document.elementFromPoint( touch.clientX, touch.clientY );

                var wordIndex = null;
                if (element !== null && element.classList.contains('word')) {
                    wordIndex = element.getAttribute('wordindex');
                }

                if (wordIndex !== null && this.ongoingSelection.length) {
                    this.updateSelection(wordIndex);
                }
            },
            updateSelection: function(wordIndex) {
                if (this.touchTimer) {
                    return;
                }

                if (wordIndex == this.ongoingSelection[0].wordIndex ||
                    wordIndex == this.ongoingSelection[this.ongoingSelection.length - 1].wordIndex) {
                        return;
                }

                var firstWordIndex = this.ongoingSelectionStartingWordIndex;
                var lastWordIndex = wordIndex;
                
                if (firstWordIndex > lastWordIndex) {
                    var firstWordIndex = wordIndex;
                    var lastWordIndex = this.ongoingSelectionStartingWordIndex;
                }
                
                if (firstWordIndex < this.ongoingSelectionStartingWordIndex - 14) {
                    firstWordIndex = this.ongoingSelectionStartingWordIndex - 14;
                }

                if (lastWordIndex - firstWordIndex > 14) {
                    lastWordIndex -= lastWordIndex - firstWordIndex - 14;
                }

                this.ongoingSelection = [];
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;

                    if (i < firstWordIndex || i > lastWordIndex) {
                        continue;
                    }

                    this.words[i].selected = true;
                    var selectedWord = {
                        word: this.words[i].word,
                        wordIndex: i,
                        uniqueWordIndex: this.getUniqueWordIndex(this.words[i].word.toLowerCase()),
                        reading: this.uniqueWords[this.getUniqueWordIndex(this.words[i].word.toLowerCase())].reading,
                        sentence_index: this.words[i].sentence_index
                    };

                    this.ongoingSelection.push(selectedWord);
                }
            },
            finishSelection: function(event) {
                if (!event.cancelable) {
                    return;
                }

                if (this.touchTimer) {
                    clearTimeout(this.touchTimer);
                    this.touchTimer = null;
                    return;
                }

                this.selectionOngoing = false;
                if (this.ongoingSelection.length == 1) {
                    // if the selected word is in an expression, select the expression instead
                    var selectedPhrase = this.getSelectedPhraseIndex();
                    var newWordSelected = this.selection.find(o => o.wordIndex == this.ongoingSelection[0].wordIndex) !== undefined;
                    var phraseIndexes = this.words[this.ongoingSelection[0].wordIndex].phraseIndexes;
                    if (phraseIndexes.length && selectedPhrase !== phraseIndexes[phraseIndexes.length - 1]) {
                        if (selectedPhrase == -1 || !newWordSelected) {
                            this.selectPhraseInstanceByWord(this.ongoingSelection[0].wordIndex, phraseIndexes[0]);
                        } else {
                            for (let i = 0; i < phraseIndexes.length; i++) {
                                if (phraseIndexes[i] == selectedPhrase && i < phraseIndexes.length - 1) {
                                    this.selectPhraseInstanceByWord(this.ongoingSelection[0].wordIndex, phraseIndexes[i + 1]);
                                    break;
                                }
                            }
                        }
                    }
                }

                // update selected word classes after automatic phrase selection
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                }

                for (let i = 0; i < this.ongoingSelection.length; i++) {
                    this.words[this.ongoingSelection[i].wordIndex].selected = true;
                }

                this.selection = this.ongoingSelection;
                this.ongoingSelection = [];

                if (this.selection.length) {
                    this.selectedPhrase = this.getSelectedPhraseIndex();                    
                    
                    this.updatePhraseBorders();
                    this.$emit('textSelected', this.selection, this.selectedPhrase, this.$props.textBlockId);
                }
            },
            unselectWord() {
                this.selectedPhrase = -1;
                this.selection = [];
            },
            getUniqueWordIndex: function(word) {
                for (var i = 0; i < this.uniqueWords.length; i++) {
                    if (this.uniqueWords[i].word == word) {
                        return i;
                    }
                }

                return -1;
            },
            getSelectedPhraseIndex: function() {
                var phraseIndex = -1;
                var selectedText = this.selection.map(a => a.word.toLowerCase()).join('');
                
                while (selectedText.indexOf('newline') !== -1) {
                    selectedText = selectedText.replace('newline', '');
                }
                

                for (let i = 0; i < this.phrases.length; i++) {
                    if (selectedText == this.phrases[i].words.join('')) {
                        phraseIndex = i;
                        break;
                    }
                }

                return phraseIndex;
            },
            updatePhraseBorders: function() {
                for (var i = 0; i < this.words.length; i++) {
                    if (this.words[i].phraseIndexes.length) {
                        var lowestPhraseStage = 1000;
                        for (let p = 0; p < this.words[i].phraseIndexes.length; p++) {
                            if (parseInt(this.phrases[this.words[i].phraseIndexes[p]].stage) < lowestPhraseStage) {
                                lowestPhraseStage = parseInt(this.phrases[this.words[i].phraseIndexes[p]].stage);
                            }
                        }

                        this.words[i].phraseStage = lowestPhraseStage;
                    }
                    
                    // phrase start
                    this.words[i].phraseStart = false;
                    this.words[i].phraseEnd = false;
                    if (this.words[i].phraseIndexes.length && (i == 0 || !this.words[i - 1].phraseIndexes.length)) {
                        this.words[i].phraseStart = true;
                    }
                    
                    // phrase end
                    if (this.words[i].phraseIndexes.length && (i + 1 == this.words.length || !this.words[i + 1].phraseIndexes.length)) {
                        this.words[i].phraseEnd = true;
                    }
                }
            },
            updateSelectedWordLookupCount: function(word, uniqueWordIndex) {
                this.uniqueWords[uniqueWordIndex].lookup_count ++;
                axios.post('/vocabulary/update', {
                    id: this.uniqueWords[uniqueWordIndex].id,
                    lookup_count: this.uniqueWords[uniqueWordIndex].lookup_count
                });

                // update all instances in text
                this.$emit('update-lookup-count', this.uniqueWords[uniqueWordIndex].id);
                for (var i  = 0; i < this.words.length; i++) {
                    if (this.words[i].word.toLowerCase() == word) {
                        this.words[i].lookup_count = this.uniqueWords[uniqueWordIndex].lookup_count;
                    }
                }
            },
        }
    }
</script>
