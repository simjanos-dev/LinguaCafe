<template>
    <div 
        class="text-block w-100 overflow-x-hidden" 
        :textblock="$props.textBlockId" 
        @mousemove="removePhraseHover"
    >
        <template>
            <template v-for="(word, wordIndex) in words"><!--
                --><div class="subtitle-timestamp rounded-pill py-1 mt-12 mb-1" v-if="word.subtitleIndex !== -1"><!--
                    -->{{ subtitleTimestamps[word.subtitleIndex].start }}:{{ subtitleTimestamps[word.subtitleIndex].end }}<!--
                --></div><!--
                --><div 
                    :key="wordIndex"
                    v-if="word.word !== 'NEWLINE' && /\S/.test(word.word)" 
                    :wordindex="wordIndex" 
                    :stage="word.stage" 
                    :phrasestage="word.phraseStage" 
                    :class="{
                        'no-highlight': hideAllHighlights || (hideNewWordHighlights && word.stage == 2),
                        'plain-text-mode': plainTextMode,
                        'word': true,
                        'highlighted': word.selected || word.hover,
                        'phrase': word.phraseIndexes.length > 0, 
                        'phrase-start': word.phraseStart, 
                        'phrase-end': word.phraseEnd,
                        'space-after': word.spaceAfter
                    }"
                    :style="{
                        'font-size': fontSize + 'px', 
                        'margin-bottom': (lineSpacing * 4) + 'px'
                    }" 
                    
                    
                    @pointerenter="hoverPhraseSelection(wordIndex);"
                    @pointerleave="updateHoveredWords(null)"
                    @touchstart="startSelectionTouch($event, word.word, wordIndex)" 
                    @mousedown.stop="startSelection($event, word.word, wordIndex)" 
                    @touchmove="updateSelectionTouch($event, wordIndex);" 
                    @mousemove.stop="updateSelectionMouse($event, wordIndex);" 
                    @touchend.stop="finishSelection($event)"
                    @mouseup.stop="finishSelection($event)"
                    @mouseleave=";"
                ><!--
                    --><template v-if="language == 'japanese'"><!--
                        --><ruby class="rubyword" :wordindex="wordIndex"><!--
                            -->{{ word.word }}<!--
                            --><rt v-if="word.stage == 2 && furiganaOnNewWords && word.reading.length && word.word !== word.reading" :style="{'font-size': (fontSize - 4) + 'px'}"><!--
                                -->{{ word.reading }}<!--
                            --></rt><!--
                            --><rt v-if="word.stage < 0 && furiganaOnHighlightedWords && word.reading.length && word.word !== word.reading" :style="{'font-size': (fontSize - 4) + 'px'}"><!--
                                -->{{ word.reading }}<!--
                            --></rt><!--
                        --></ruby>
                    </template><!--
                    --><template v-if="language !== 'japanese'">{{ word.word }}</template><!--
                    --><template v-if="plainTextMode && word.spaceAfter">&nbsp;</template><!--
                --></div><!--
                --><br v-if="word.word == 'NEWLINE'"><!--
            --></template>
        </template>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                phraseLengthLimit: 14,
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
            subtitleTimestamps: Array,
            language: String,
            hideAllHighlights: Boolean,
            hideNewWordHighlights: Boolean,
            plainTextMode: Boolean,
            fontSize: Number,
            lineSpacing: Number,
            furiganaOnHighlightedWords: Boolean,
            furiganaOnNewWords: Boolean,
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
                // collection for hover vocabulary box
                var hoveredWords = [];
                var hoveredPhraseIndex = -1;

                this.removePhraseHover();
                var phraseIndexes = this.words[wordIndex].phraseIndexes;
                if (!phraseIndexes.length) {

                    // update hovered words
                    var word = JSON.parse(JSON.stringify(this.words[wordIndex]));
                    word.hover = true;
                    hoveredWords.push(word);
                    hoveredWords[0].wordIndex = wordIndex;
                    this.updateHoveredWords(hoveredWords);

                    return;
                } else {
                    hoveredPhraseIndex = this.words[wordIndex].phraseIndexes[0];
                }

                // find the first word of the phrase
                var currentWordIndex = wordIndex;
                while (currentWordIndex > 0 && (this.words[currentWordIndex - 1].word == 'NEWLINE' || this.words[currentWordIndex - 1].phraseIndexes.some(el => phraseIndexes.includes(el)))) {
                    currentWordIndex--;
                }
                
                // highlight the phrase
                do {
                    this.words[currentWordIndex].hover = true;

                    // add words for hover vocabulary box
                    if (this.words[currentWordIndex].phraseIndexes.includes(hoveredPhraseIndex) && this.words[currentWordIndex].word !== 'NEWLINE') {
                        hoveredWords.push(this.words[currentWordIndex]);
                        hoveredWords[hoveredWords.length - 1].wordIndex = currentWordIndex;
                    }

                    currentWordIndex ++;
                } while(currentWordIndex < this.words.length && (this.words[currentWordIndex].word == 'NEWLINE' || this.words[currentWordIndex].phraseIndexes.some(el => phraseIndexes.includes(el))));

                this.updateHoveredWords(hoveredWords, hoveredPhraseIndex);
            },
            removePhraseHover: function() {
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].hover = false;
                }
            },
            updateHoveredWords: function(hoveredWords, hoveredPhraseIndex) {
                var data = {
                    textBlockId: this.$props.textBlockId,
                    hoveredWords: JSON.parse(JSON.stringify(hoveredWords)),
                    translation: '',
                    reading: '',
                };

                if (hoveredWords !== null && hoveredWords.length === 1) {
                    var uniqueWordIndex = this.getUniqueWordIndex(hoveredWords[0].word.toLowerCase());
                    var uniqueWord = this.uniqueWords[uniqueWordIndex];
                    
                    data.translation = uniqueWord.translation;  
                    data.reading = uniqueWord.reading;
                    data.hoveredWords[0].lemma = uniqueWord.base_word;
                }

                if (hoveredWords !== null && hoveredWords.length > 1) {
                    data.translation = this.phrases[hoveredPhraseIndex].translation;
                    data.reading = this.phrases[hoveredPhraseIndex].reading;
                }
                

                this.$emit('updateHoveredWords', data);
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
                        var uniqueWordIndex = this.getUniqueWordIndex(this.words[currentWordIndex].word.toLowerCase());
                        var uniqueWord = this.uniqueWords[uniqueWordIndex];
                        newSelection.push({
                            word: this.words[currentWordIndex].word,
                            reading: uniqueWord.reading,
                            kanji: uniqueWord.kanji,
                            sentence_index: this.words[currentWordIndex].sentence_index,
                            wordIndex: currentWordIndex,
                            uniqueWordIndex: uniqueWordIndex,
                            spaceAfter: this.words[currentWordIndex].spaceAfter,
                        });
                    }

                    currentWordIndex ++;
                } while(currentWordIndex < this.words.length && (this.words[currentWordIndex].word == 'NEWLINE' || this.words[currentWordIndex].phraseIndexes.includes(phraseIndex)));
                
                this.ongoingSelection = newSelection;
            },
            startSelectionTouch: function(event, wordText, wordIndex) {
                if (this.$props.plainTextMode) {
                    return;
                }

                this.touchTimer = setTimeout(() => {
                    this.startSelection(event, wordText, wordIndex);
                }, 500);
            },
            startSelection: function(event, wordText, wordIndex) {
                if (this.$props.plainTextMode) {
                    return;
                }

                this.$emit('startSelection');

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

                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                }
                
                // set selected word 
                var selectedWord = {
                    word: wordText,
                    spaceAfter: this.words[wordIndex].spaceAfter,
                    wordIndex: wordIndex,
                    sentence_index: this.words[wordIndex].sentence_index
                };
                
                
                this.ongoingSelection = [selectedWord];
                this.words[wordIndex].selected = true;
                this.ongoingSelectionStartingWordIndex = wordIndex;
                
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
                if (element !== null && element.classList.contains('word') || element.classList.contains('rubyword')) {
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
                    (wordIndex < this.ongoingSelection[0].wordIndex && this.ongoingSelection.length == this.phraseLengthLimit) ||
                    (wordIndex > this.ongoingSelection[this.ongoingSelection.length - 1].wordIndex && this.ongoingSelection.length == this.phraseLengthLimit) ||
                    wordIndex == this.ongoingSelection[this.ongoingSelection.length - 1].wordIndex) {
                        return;
                }

                var firstWordIndex = this.ongoingSelectionStartingWordIndex;
                var lastWordIndex = wordIndex;
                
                if (firstWordIndex > lastWordIndex) {
                    var firstWordIndex = wordIndex;
                    var lastWordIndex = this.ongoingSelectionStartingWordIndex;
                }
                
                if (firstWordIndex < this.ongoingSelectionStartingWordIndex - this.phraseLengthLimit + 1) {
                    firstWordIndex = this.ongoingSelectionStartingWordIndex - this.phraseLengthLimit + 1;
                }

                if (lastWordIndex - firstWordIndex > this.phraseLengthLimit + 1) {
                    lastWordIndex -= lastWordIndex - firstWordIndex - this.phraseLengthLimit + 1;
                }

                this.ongoingSelection = [];
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;

                    if (i < firstWordIndex || i > lastWordIndex || this.words[i].word === 'NEWLINE') {
                        continue;
                    }

                    this.words[i].selected = true;
                    var selectedWord = {
                        word: this.words[i].word,
                        wordIndex: i,
                        sentence_index: this.words[i].sentence_index,
                        spaceAfter: this.words[i].spaceAfter,
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

                // set words to selected, and collect their information
                for (let i = 0; i < this.ongoingSelection.length; i++) {
                    this.words[this.ongoingSelection[i].wordIndex].selected = true;
                    var uniqueWordIndex = this.getUniqueWordIndex(this.ongoingSelection[i].word.toLowerCase());
                    this.ongoingSelection[i].uniqueWordIndex = uniqueWordIndex;
                    this.ongoingSelection[i].reading = this.uniqueWords[uniqueWordIndex].reading;
                    this.ongoingSelection[i].kanji = this.uniqueWords[uniqueWordIndex].kanji;
                }
                
                this.selection = this.ongoingSelection;
                this.ongoingSelection = [];

                if (this.selection.length) {
                    this.selectedPhrase = this.getSelectedPhraseIndex();

                    if (this.selection.length == 1 || this.selectedPhrase !== -1) {
                        this.updateLookupCount(this.selection[0].word);
                    }
                    
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
            /*
                Emits an event to TextBlockGroup, which will run updateWordLookupCount or 
                updatePhraseLookupCount on all TextBlocks.
            */
            updateLookupCount(word) {
                if (this.selection.length == 1) {
                    this.$emit('updateLookupCount', 'word', word, null);
                } else if (this.selectedPhrase !== -1) {
                    this.$emit('updateLookupCount', 'phrase', null, this.phrases[this.selectedPhrase].id);
                }
            },
            /*
                Updates the lookup count of all instances of a word in the text.
            */
            updateWordLookupCount(word) {
                let uniqueWordIndex = this.getUniqueWordIndex(word);
                
                if (uniqueWordIndex === -1) {
                    return;
                }

                this.uniqueWords[uniqueWordIndex].lookup_count ++;
                for (var i  = 0; i < this.words.length; i++) {
                    if (this.words[i].word.toLowerCase() == word) {
                        this.words[i].lookup_count ++;
                    }
                }
            },
            /*
                Updates the lookup count of a phrase.
            */
            updatePhraseLookupCount(phraseId) {
                for (var i  = 0; i < this.phrases.length; i++) {
                    if (this.phrases[i].id == phraseId) {
                        this.phrases[i].lookup_count ++;
                    }
                }
            },
        }
    }
</script>
