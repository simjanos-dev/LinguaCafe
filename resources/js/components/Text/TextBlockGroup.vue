<template>
    <div 
        :class="{
            'text-block-group': true,
            'w-100': true,
            'chinese-font': language == 'chinese'
        }"
    >
        <!-- Anki api notifications -->
        <v-snackbar 
            :value="true" 
            right 
            top 
            :light="$props.theme == 'light'"
            :dark="$props.theme == 'dark'"
            color="foreground" 
            class="anki-snackbar rounded-lg mr-2"
            height="108"
            :style="{'margin-top': ((snackBarIndex) * 124 + 16) + 'px'}"
            :timeout="-1"
            v-for="(snackBar, snackBarIndex) in snackBars"
            :key="'snackbar-' + snackBarIndex"
        >
            <div class="pl-3 pr-2 pt-1 d-flex font-weight-bold snackbar-title">
                <v-icon v-if="snackBar.type !== 'insert success' && snackBar.type !== 'update success'" color="error" class="mr-2">mdi-alert</v-icon>
                <v-icon v-else color="success" class="mr-2">mdi-cards</v-icon>

                <template v-if="snackBar.type =='error'">Anki error</template>
                <template v-if="snackBar.type =='insert success'">Added to anki</template>
                <template v-if="snackBar.type =='update success'">Updated in anki</template>

                <v-spacer />
                <v-btn icon>
                    <v-icon @click="removeSnackbar(snackBar.id)">mdi-close</v-icon>
                </v-btn>
            </div>
            <div class="py-2 px-4">
                {{ snackBar.content }}
            </div>
        </v-snackbar>

        <slot
            :textBlocks="textBlocks"
            :language="language"
            :hideAllHighlights="hideAllHighlights"
            :hideNewWordHighlights="hideNewWordHighlights"
            :plainTextMode="plainTextMode"
            :fontSize="fontSize"
            :lineSpacing="lineSpacing"
            :furiganaOnHighlightedWords="furiganaOnHighlightedWords"
            :furiganaOnNewWords="furiganaOnNewWords"
            :updateSelection="updateSelection"
            :unselectAllWords="unselectAllWords"
            :updateLookupCount="updateLookupCount"
            :startSelection="startSelection"
            :updateHoveredWords="updateHoverVocabularyBox"
        >
            <text-block
                v-for="textBlock in textBlocks"
                :key="textBlock.id"
                ref="textBlock"
                :textBlockId="textBlock.id"
                :_words="textBlock.words"
                :_phrases="textBlock.phrases"
                :_uniqueWords="textBlock.uniqueWords"
                :language="language"
                :hideAllHighlights="hideAllHighlights"
                :hideNewWordHighlights="hideNewWordHighlights"
                :plainTextMode="plainTextMode"
                :fontSize="fontSize"
                :lineSpacing="lineSpacing"
                :furiganaOnHighlightedWords="furiganaOnHighlightedWords"
                :furiganaOnNewWords="furiganaOnNewWords"
                @textSelected="updateSelection"
                @unselectAllWords="unselectAllWords"
                @updateLookupCount="updateLookupCount"
                @startSelection="startSelection"
                @updateHoveredWords="updateHoverVocabularyBox"
            ></text-block>
        </slot>

        <!--Vocabulary popup box-->
        <vocabulary-hover-box
            v-if="hoverVocabBox.active && (($props.vocabularySidebar && $props.vocabularySidebarFits) || !vocabBox.active)"
            :key="'vocabulary-hover-box' + hoverVocabBox.key"
            :translation="hoverVocabBox.translation"
            :positionLeft="hoverVocabBox.positionLeft"
            :positionTop="hoverVocabBox.positionTop"
            :reading="hoverVocabBox.reading"
        ></vocabulary-hover-box>

        <!--Vocabulary popup box-->
        <vocabulary-box
            v-if="(!$props.vocabularySidebar || !$props.vocabularySidebarFits) && vocabBox.active"
            ref="vocabularyBox"
            :language="$props.language"
            :active="vocabBox.active"
            :type="vocabBox.type"
            :positionLeft="vocabBox.positionLeft"
            :positionTop="vocabBox.positionTop"
            :width="vocabBox.width"
            :kanjiList="vocabBox.kanjiList"
            :word="vocabBox.word"
            :phrase="vocabBox.phrase"
            :stage="vocabBox.stage"
            :_reading="vocabBox.reading"
            :_baseWord="vocabBox.baseWord"
            :_baseWordReading="vocabBox.baseWordReading"
            :_phraseReading="vocabBox.phraseReading"
            :_translationText="vocabBox.translationText"
            :_searchField="vocabBox.searchField"
            @setStage="setStage"
            @unselectAllWords="unselectAllWords"
            @updateVocabBoxData="updateVocabBoxData"
            @addNewPhrase="addNewPhrase"
            @deletePhrase="deletePhrase"
            @addSelectedWordToAnki="addSelectedWordToAnki"
        ></vocabulary-box>

        <!--Vocabulary sidebar-->
        <vocabulary-side-box
            v-if="$props.vocabularySidebarFits && $props.vocabularySidebar && !vocabBox.sidebarHidden"
            :key="'vocabulary-side-box-' + vocabBox.key"
            ref="vocabularySideBox"
            :language="$props.language"
            :active="vocabBox.active"
            :type="vocabBox.type"
            :positionLeft="vocabBox.positionLeft"
            :positionTop="vocabBox.positionTop"
            :height="vocabBox.height"
            :kanjiList="vocabBox.kanjiList"
            :word="vocabBox.word"
            :phrase="vocabBox.phrase"
            :stage="vocabBox.stage"
            :_reading="vocabBox.reading"
            :_baseWord="vocabBox.baseWord"
            :_baseWordReading="vocabBox.baseWordReading"
            :_phraseReading="vocabBox.phraseReading"
            :_translationText="vocabBox.translationText"
            :_searchField="vocabBox.searchField"
            @setStage="setStage"
            @unselectAllWords="unselectAllWords"
            @updateVocabBoxData="updateVocabBoxData"
            @addNewPhrase="addNewPhrase"
            @deletePhrase="deletePhrase"
            @addSelectedWordToAnki="addSelectedWordToAnki"
        ></vocabulary-side-box>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                snackBars: [
                ],
                snackbarId: 1,
                ankiAutoAddCards: false,
                ankiShowNotifications: false,
                textBlocks: this.$props._textBlocks,
                hoverVocabBox: {
                    active: false,
                    key: 0,
                    hoveredWords: null,
                    hoveredPhrase: -1,
                    reading: '',
                    translation: '',
                    positionLeft: 0,
                    positionTop: 0,
                },
                vocabBox: {
                    /*
                        Keep the sidebar hidden until the first position
                        update, so it won't jump around on the screen when
                        a text is opened.
                    */
                    sidebarHidden: true,

                    key: 0,
                    active: false,
                    // word, new phrase, existing phrase
                    type: 'empty',

                    // data for word
                    word: '',
                    reading: '',
                    baseWord: '',
                    baseWordReading: '',
                    stage: 0,

                    // data for phrase
                    phrase: [],
                    phraseReading: '',

                    // data for both
                    kanjiList: [],
                    translationText: '',
                    translationList: [],

                    // ui data
                    tab: 0,
                    width: 400,
                    positionLeft: 0,
                    positionTop: 0,
                    height: 0,
                    searchField: '',
                    searchResults: [],
                },
                selection: [],
                selectedPhrase: -1,
                selectedTextBlock: -1,
                phraseCurrentlySaving: false,
            }
        },
        props: {
            textType: {
                type: String,
                default: 'simple-text',
            },
            theme: String,
            fullscreen: Boolean,
            _textBlocks: Array,
            language: String,
            hideAllHighlights: {
                type: Boolean,
                default: false
            },
            hideNewWordHighlights: {
                type: Boolean,
                default: false
            },
            plainTextMode: {
                type: Boolean,
                default: false
            },
            fontSize: Number,
            lineSpacing: {
                type: Number,
                default: 0
            },
            vocabBoxScrollIntoView: {
                type: String,
                default: 'Disabled'
            },
            furiganaOnHighlightedWords: {
                type: Boolean,
                default: false
            },
            furiganaOnNewWords: {
                type: Boolean,
                default: false
            },
            vocabularySidebar: {
                type: Boolean,
                default: false
            },
            vocabularySidebarFits: {
                type: Boolean,
                default: true
            },
            hotkeysEnabled: {
                type: Boolean,
                default: false
            },
            vocabularyHoverBox: {
                type: Boolean,
                default: false
            }
        },
        watch: {
            _textBlocks: function(newVal, oldVal) {
                this.textBlocks = newVal;
            }
        },
        mounted() {
            window.addEventListener('keydown', this.hotkeyHandle);
            window.addEventListener('resize', this.updateVocabBoxPositionDelay);
            window.addEventListener('mouseup', this.unselectAllWords);

            axios.post('/settings/get-by-name', {
                'settingNames': [
                    'ankiAutoAddCards',
                    'ankiShowNotifications'
                ]
            }).then((response) => {
                this.ankiAutoAddCards = response.data.ankiAutoAddCards;
                this.ankiShowNotifications = response.data.ankiShowNotifications;
            });

            this.updateVocabBoxPositionDelay();
        },  
        beforeDestroy() {
            window.removeEventListener('resize', this.updateVocabBoxPositionDelay);
            window.removeEventListener('mouseup', this.unselectAllWords);
            window.removeEventListener('keydown', this.hotkeyHandle);
        },
        methods: {
            hotkeyHandle(event) {
                if (!this.$props.hotkeysEnabled) {
                    return;
                }
                
                switch(event.which) {
                    // set stage 0-7
                    case 48:
                    case 49:
                    case 50:
                    case 51:
                    case 52:
                    case 53:
                    case 54:
                    case 55:
                        event.preventDefault();
                        this.setStage(48 - event.which);
                        break;

                    // set stage to ignore
                    case 88:
                        event.preventDefault();
                        if (this.selection.length == 1) {
                            this.setStage(1);
                        }
                        break;

                    // increase font size
                    case 38:
                    case 87:
                        event.preventDefault();
                        this.$emit('increase-font-size');
                        break;
                    
                    // decrease font size
                    case 40:
                    case 83:
                        event.preventDefault();
                        this.$emit('decrease-font-size');
                        break;

                    // add selected word to anki
                    case 70:
                        event.preventDefault();
                        this.addSelectedWordToAnki();
                        break;

                    // unselect all words
                    case 27:
                        event.preventDefault();
                        this.unselectAllWords();
                        break;

                    // previous
                    case 37:
                    case 65:
                        event.preventDefault();
                        this.selectPreviousWord(event.ctrlKey, event.shiftKey);
                        break;

                    // next
                    case 39:
                    case 68:
                        event.preventDefault();
                        this.selectNextWord(event.ctrlKey, event.shiftKey);
                        break;
                }
            },
            selectPreviousWord(newWordOnly, highlightedWordOnly) {
                if (!this.selection.length) {
                    var currentWordIndex = this.textBlocks[0].words.length - 1;
                } else {
                    var currentWordIndex = this.selection[0].wordIndex;
                }

                var wordToSelect = -1;
                var selectedTextBlock = this.selectedTextBlock === -1 ? 0 : this.selectedTextBlock;

                // there are no previous words
                if (currentWordIndex == 0) {
                    return;
                }

                // go through the text backwards, and find a word to select 
                for (var wordIndex = currentWordIndex - 1; wordIndex >= 0; wordIndex--) {
                    // skip not displayed whitespace words
                    if (document.querySelector('.text-block[textblock="' + this.textBlocks[selectedTextBlock].id + '"] .word[wordindex="' + wordIndex  + '"]') === null) {
                        continue;
                    }

                    // select the previous word if it's a simple arrow key press
                    if (!newWordOnly && !highlightedWordOnly) {
                        wordToSelect = wordIndex;
                        break;
                    }

                    // select the previous new word
                    if (newWordOnly && this.textBlocks[selectedTextBlock].words[wordIndex].stage == 2) {
                        wordToSelect = wordIndex;
                        break;
                    }

                    // select the previous highlighted word
                    if (highlightedWordOnly && this.textBlocks[selectedTextBlock].words[wordIndex].stage < 0) {
                        wordToSelect = wordIndex;
                        break;
                    }
                }

                // return if no selectable word was found
                if (wordToSelect === -1) {
                    return;
                }
                
                // select the new word
                this.unselectAllWords();
                this.$nextTick(() => {
                    var wordElement = document.querySelector('.text-block[textblock="' + this.textBlocks[selectedTextBlock].id + '"] .word[wordindex="' + wordToSelect  + '"]');
                    var mouseDownEvent = new CustomEvent('mousedown', {cancelable: true});
                    var mouseUpEvent = new CustomEvent('mouseup', {cancelable: true});
                    wordElement.dispatchEvent(mouseDownEvent);
                    wordElement.dispatchEvent(mouseUpEvent);
                });
            },
            selectNextWord(newWordOnly, highlightedWordOnly) {
                if (!this.selection.length) {
                    var currentWordIndex = 0;
                } else {
                    var currentWordIndex = this.selection[this.selection.length - 1].wordIndex;
                }

                var wordToSelect = -1;
                var selectedTextBlock = this.selectedTextBlock === -1 ? 0 : this.selectedTextBlock;

                // there are no next words to select
                if (currentWordIndex == this.textBlocks[selectedTextBlock].words.length - 1) {
                    return;
                }

                // go through the text forward, and find a word to select 
                for (var wordIndex = currentWordIndex + 1; wordIndex < this.textBlocks[selectedTextBlock].words.length; wordIndex++) {
                    // skip not displayed whitespace words
                    if (document.querySelector('.text-block[textblock="' + this.textBlocks[selectedTextBlock].id + '"] .word[wordindex="' + wordIndex  + '"]') === null) {
                        continue;
                    }

                    // select the previous word if it's a simple arrow key press
                    if (!newWordOnly && !highlightedWordOnly) {
                        wordToSelect = wordIndex;
                        break;
                    }

                    // select the previous new word
                    if (newWordOnly && this.textBlocks[selectedTextBlock].words[wordIndex].stage == 2) {
                        wordToSelect = wordIndex;
                        break;
                    }

                    // select the previous highlighted word
                    if (highlightedWordOnly && this.textBlocks[selectedTextBlock].words[wordIndex].stage < 0) {
                        wordToSelect = wordIndex;
                        break;
                    }

                }

                // return if no selectable word was found
                if (wordToSelect === -1) {
                    return;
                }

                // select the new word
                this.unselectAllWords();
                this.$nextTick(() => {
                    var wordElement = document.querySelector('.text-block[textblock="' + this.textBlocks[selectedTextBlock].id + '"] .word[wordindex="' + wordToSelect  + '"]');
                    var mouseDownEvent = new CustomEvent('mousedown', {cancelable: true});
                    var mouseUpEvent = new CustomEvent('mouseup', {cancelable: true});
                    wordElement.dispatchEvent(mouseDownEvent);
                    wordElement.dispatchEvent(mouseUpEvent);
                });
            },
            updateSelection(newSelection, newSelectedPhrase, textBlockId) {
                this.vocabBox.tab = 0;
                this.selection = newSelection;
                this.selectedPhrase = newSelectedPhrase;
                this.selectedTextBlock = textBlockId;
                this.vocabBox.active = true;

                // update vocab box data
                this.vocabBox.key ++;

                this.vocabBox.searchField = '';
                this.vocabBox.translationText = '';
                this.vocabBox.word = '';
                this.vocabBox.phrase = [];
                this.vocabBox.reading = '';
                this.vocabBox.kanjiList = [];
                this.vocabBox.baseWord = '';
                this.vocabBox.baseWordReading = '';

                
                if (this.selection.length == 1) {
                    var uniqueWord = this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex];
                    this.vocabBox.type = 'word';
                    this.vocabBox.word = uniqueWord.word;
                    this.vocabBox.reading = uniqueWord.reading;
                    this.vocabBox.baseWord = uniqueWord.base_word;
                    this.vocabBox.baseWordReading = uniqueWord.base_word_reading;
                    this.vocabBox.translationText = uniqueWord.translation;
                    this.vocabBox.stage = uniqueWord.stage;
                    if (uniqueWord.base_word !== '') {
                        this.vocabBox.searchField = uniqueWord.base_word;

                        // remove unnecessary parts of the search term
                        this.vocabBox.searchField = this.trimSearchTerm(this.vocabBox.searchField);
                    } else {
                        this.vocabBox.searchField = uniqueWord.word;
                    }
                } else {
                    if (this.selectedPhrase !== -1) {
                        this.vocabBox.type = 'phrase';
                        this.vocabBox.reading = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].reading;
                        this.vocabBox.translationText = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].translation;
                        this.vocabBox.stage = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage;
                    } else {
                        this.vocabBox.type = 'new-phrase';
                    }

                    for (let i = 0; i < this.selection.length; i++) {
                        if (this.selection[i].word.toLowerCase() == 'newline') {
                            continue;
                        }
                     
                        if (this.selection.length > 1) {
                            this.vocabBox.phrase.push(this.selection[i]);
                        }

                        this.vocabBox.searchField += this.selection[i].word;
                        if (this.selection[i].spaceAfter) {
                            this.vocabBox.searchField += ' ';
                        }

                        if (this.selectedPhrase == -1) {
                            this.vocabBox.reading += this.selection[i].reading;
                        }
                    }
                }

                // collect unique kanji
                for (let wordIndex = 0; wordIndex < this.selection.length; wordIndex ++) {
                    var kanji = this.selection[wordIndex].kanji;
                    for (let kanjiIndex = 0; kanjiIndex < kanji.length; kanjiIndex ++) {
                        if (this.vocabBox.kanjiList.indexOf(kanji[kanjiIndex]) === -1) {
                            this.vocabBox.kanjiList.push(kanji[kanjiIndex]);
                        }
                    }
                }

                this.updateVocabBoxPosition();
                this.vocabBox.key ++;
            },
            updateHoverVocabularyBox(data) {
                if (!this.$props.vocabularyHoverBox || data.hoveredWords === null) {
                    this.hoverVocabBox.active = false;
                    this.hoverVocabBox.positionLeft = 0;
                    this.hoverVocabBox.positionTop = 0;
                    this.hoverVocabBox.translation = '';
                    this.hoverVocabBox.reading = '';
                    
                    return;
                } else {
                    this.hoverVocabBox.hoveredWords = data.hoveredWords;
                    this.hoverVocabBox.key ++;
                    this.hoverVocabBox.hoveredPhrase = -1;
                    this.hoverVocabBox.translation = data.translation;
                    this.hoverVocabBox.reading = data.reading;
                    this.hoverVocabBox.active = (data.translation.length > 0 || data.reading.length > 0);
                }

                var margin = 8;
                var hoverVocabBoxWidth = 240;
                var vocabBoxAreaElement = document.getElementsByClassName('vocab-box-area')[0];
                var vocabBoxArea = vocabBoxAreaElement.getBoundingClientRect();


                if (data.hoveredWords.length == 1) {
                    var hoveredWordPositions = document.querySelector('[textblock="' + data.textBlockId + '"] [wordindex="' + data.hoveredWords[0].wordIndex + '"]').getBoundingClientRect();
                } else {
                    var hoveredWordPositions = document.querySelector('[textblock="' + data.textBlockId + '"] [wordindex="' + data.hoveredWords[parseInt(data.hoveredWords.length / 2)].wordIndex + '"]').getBoundingClientRect();
                }

                var hoveredWordPositions = document.querySelector('[textblock="' + data.textBlockId + '"] [wordindex="' + data.hoveredWords[0].wordIndex + '"]').getBoundingClientRect();
                
                this.hoverVocabBox.positionLeft = hoveredWordPositions.right - vocabBoxArea.left - hoverVocabBoxWidth / 2 - (hoveredWordPositions.right - hoveredWordPositions.left) / 2;
                if (this.hoverVocabBox.positionLeft < margin) {
                    this.hoverVocabBox.positionLeft = margin;
                } else if (this.hoverVocabBox.positionLeft > vocabBoxArea.right - vocabBoxArea.left - hoverVocabBoxWidth - margin) {
                    this.hoverVocabBox.positionLeft = vocabBoxArea.right - vocabBoxArea.left - hoverVocabBoxWidth - margin;
                }

                this.hoverVocabBox.positionTop = hoveredWordPositions.bottom - vocabBoxArea.top + vocabBoxAreaElement.scrollTop + 25;
            },
            startSelection() {
                if (this.$refs.vocabularyBox !== undefined) {
                    this.$refs.vocabularyBox.inputChanged();
                }

                if (this.$refs.vocabularySideBox !== undefined) {
                    this.$refs.vocabularySideBox.inputChanged();
                }

                if (this.selection.length == 1) {
                    this.saveWord();
                } else if (this.selectedPhrase !== -1) {
                    this.savePhrase();
                }
                
                this.vocabBox.active = false;
            },
            unselectAllWords() {
                this.vocabBox.active = false;
                if (this.selection.length == 1) {
                    this.saveWord();
                } else if (this.selectedPhrase !== -1) {
                    this.savePhrase();
                }

                this.selectedPhrase = -1;
                this.selection = [];
                
                this.unselectAllWordsProcess();
                this.$forceUpdate();
            },
            unselectAllWordsProcess() {
                this.selectedPhrase = -1;
                this.selection = [];
                this.vocabBox.key ++;
                this.vocabBox.kanjiList = [];
                this.vocabBox.stage = 2;
                this.vocabBox.type = 'empty';
                this.vocabBox.word = '';
                this.vocabBox.phrase = [];
                this.vocabBox.searchField = '';
                this.vocabBox.translationText = '';
                this.vocabBox.reading = '';
                this.vocabBox.baseWord = '';
                this.vocabBox.baseWordReading = '';
                
                for (let j = 0; j < this.textBlocks.length; j++) {
                    this.unselectWordInTextBlock(j);
                    for (let i  = 0; i < this.textBlocks[j].words.length; i++) {
                        this.textBlocks[j].words[i].selected = false;
                        this.textBlocks[j].words[i].hover = false;
                    }
                }
            },
            unselectWordInTextBlock(textBlockId) {
                for (var i = 0; i < this.$children.length; i++) {
                    if (this.$children[i].textBlockId === undefined) {
                        continue;
                    }

                    if (this.$children[i].textBlockId === textBlockId) {
                        this.$children[i].unselectWord();
                    }
                }
            },
            updateLookupCount(type, word, phraseId) {
                for (var i = 0; i < this.$children.length; i++) {
                    if (this.$children[i].textBlockId === undefined) {
                        continue;
                    }

                    if (type === 'word') {
                        this.$children[i].updateWordLookupCount(word);
                    } else {
                        this.$children[i].updatePhraseLookupCount(phraseId);
                    }
                }

            },
            updateSelectedWordLookupCount(id) {

            },
            addSelectedWordToAnki() {
                if (this.selection.length === 0 || (this.selection.length > 1 && this.selectedPhrase === -1)) {
                    return;
                }

                // get example sentence and add space. 
                var exampleSentence = this.getExampleSentence(true);
                var exampleSentenceText = '';
                for (let wordIndex = 0; wordIndex < exampleSentence.length; wordIndex++) {
                    exampleSentenceText += exampleSentence[wordIndex].word;
                }

                if (this.selection.length == 1) {
                    var data = {
                        word: this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].word,
                        reading: this.vocabBox.reading,
                        translation: this.vocabBox.translationText,
                        exampleSentence: exampleSentenceText,
                    };
                } else {
                    let wordsText = '';
                    for (let wordIndex = 0; wordIndex < this.selection.length; wordIndex ++) {
                        wordsText += this.selection[wordIndex].word;
                        if (this.selection[wordIndex].spaceAfter) {
                            wordsText += ' ';
                        }
                    }
                    
                    var data = {
                        word: wordsText,
                        reading: this.vocabBox.reading,
                        translation: this.vocabBox.translationText,
                        exampleSentence: exampleSentenceText
                    };
                }

                axios.post('/anki/add-card', data).catch(() => {
                }).then((response) => {
                    if (!this.ankiShowNotifications) {
                        return;
                    }

                    if (response.data == 'success') {
                        this.snackBars.push({id: this.snackbarId, content: data.word, type: 'insert success'});
                    } else if (response.data == 'update success') {
                        this.snackBars.push({id: this.snackbarId, content: data.word, type: 'update success'});
                    } else {
                        this.snackBars.push({id: this.snackbarId, content: data.word + ': ' + response.data, type: 'error'});
                    }
                    
                    var snackbarToRemove = this.snackbarId;
                    this.snackbarId ++;
                    setTimeout(() => {
                        this.removeSnackbar(snackbarToRemove);
                    }, 5000);
                });
            },
            removeSnackbar(snackbarId) {
                for (let snackBarIndex = 0; snackBarIndex < this.snackBars.length; snackBarIndex++) {
                    if (this.snackBars[snackBarIndex].id == snackbarId) {
                        this.snackBars.splice(snackBarIndex, 1);
                    }
                }
            },
            addNewPhrase() {
                // create phrase object
                var phrase = {
                    id: -1,
                    stage: 0,
                    words: [],
                    reading: this.vocabBox.reading,
                    translation: '',
                };

                for (var i = 0; i < this.selection.length; i++) {
                    if (this.selection[i].word.toLowerCase() == 'newline') {
                        continue;
                    }
                    
                    phrase.words.push(this.selection[i].word.toLowerCase());
                }

                // find all instance of the new phrase in the text
                for (let j = 0; j < this.textBlocks.length; j++) {
                    var phraseOccurences = [];
                    for (var i = 0; i < this.textBlocks[j].words.length; i++) {
                        // check if the current word is the start of the phrase
                        if (this.textBlocks[j].words[i].word.toLowerCase() == phrase.words[0]) {
                            phraseOccurences.push([
                                {
                                    word: this.textBlocks[j].words[i].word.toLowerCase(),
                                    wordIndex: i,
                                    newLineCount: 0
                                }
                            ]);
                        }

                        // check if the current word is the continuation of a phrase
                        for (let p = 0 ; p < phraseOccurences.length; p++) {
                            if (phraseOccurences[p].length == phrase.words.length) {
                                continue;
                            }

                            if (phrase.words[phraseOccurences[p].length] == this.textBlocks[j].words[i].word.toLowerCase() &&
                                (i - 1) == phraseOccurences[p][phraseOccurences[p].length - 1].wordIndex + phraseOccurences[p][phraseOccurences[p].length - 1].newLineCount) {
                                phraseOccurences[p].push({
                                    word: this.textBlocks[j].words[i].word.toLowerCase(),
                                    wordIndex: i,
                                    newLineCount: 0
                                });
                            }
 
                            // count 'NEWLINE' words for comparison
                            if (this.textBlocks[j].words[i].word.toLowerCase() == 'newline') {
                                phraseOccurences[p][phraseOccurences[p].length - 1].newLineCount ++;
                            }
                        }

                    }

                    // mark all instance of the new phrase in the text
                    for (let p = 0 ; p < phraseOccurences.length; p++) {
                        if (phraseOccurences[p].length < phrase.words.length) {
                            continue;
                        }

                        for (let i = 0; i < phraseOccurences[p].length; i++) {
                            this.textBlocks[j].words[phraseOccurences[p][i].wordIndex].phraseIndexes.push(this.textBlocks[j].phrases.length);
                        }
                    }

                    this.textBlocks[j].phrases.push(JSON.parse(JSON.stringify(phrase)));
                }

                this.updatePhraseBorders();
                this.selectedPhrase = this.getSelectedPhraseIndex();

                this.updateSelectedWordStage();
                this.updateVocabBoxPosition();
                this.savePhrase();
                this.vocabBox.type = 'phrase';
            },
            getSelectedPhraseIndex() {
                var phraseIndex = -1;
                var selectedText = this.selection.map(a => a.word.toLowerCase()).join('');
                
                while (selectedText.indexOf('newline') !== -1) {
                    selectedText = selectedText.replace('newline', '');
                }
                

                for (let i = 0; i < this.textBlocks[this.selectedTextBlock].phrases.length; i++) {
                    if (selectedText == this.textBlocks[this.selectedTextBlock].phrases[i].words.join('')) {
                        phraseIndex = i;
                        break;
                    }
                }

                return phraseIndex;
            },
            deletePhrase() {
                if (this.selectedPhrase == -1) {
                    return;
                }

                var deletedPhraseId = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].id;
                for (var j  = 0; j < this.textBlocks.length; j++) {
                    var deletedPhraseIndex = this.textBlocks[j].phrases.map(e => e.id).indexOf(deletedPhraseId);
                    if (deletedPhraseIndex == -1) {
                        continue;
                    }
                    
                    for (var i  = 0; i < this.textBlocks[j].words.length; i++) {
                        // remove phrase index from words
                        for (var p = this.textBlocks[j].words[i].phraseIndexes.length - 1; p >= 0; p--) {
                            if (this.textBlocks[j].words[i].phraseIndexes[p] == deletedPhraseIndex) {
                                this.textBlocks[j].words[i].phraseIndexes.splice(p, 1);
                                break;
                            }
                        }

                        // decrease phrase indexes larger than the deleted one
                        for (var p = this.textBlocks[j].words[i].phraseIndexes.length - 1; p >= 0; p--) {
                            if (this.textBlocks[j].words[i].phraseIndexes[p] > deletedPhraseIndex) {
                                this.textBlocks[j].words[i].phraseIndexes[p] --;
                            }
                        }
                    }
                    
                    // delete phrase
                    this.textBlocks[j].phrases.splice(deletedPhraseIndex, 1);
                }

                axios.post('/vocabulary/phrase/delete', {
                    id: deletedPhraseId
                }).then(function (response) {
                });


                this.selectedPhrase = -1;
                this.selection = [];
                this.unselectAllWords();
                this.updatePhraseBorders();
            },
            savePhrase(withStage = false, exampleSentenceChanged = false) {
                if (this.phraseCurrentlySaving) {
                    return;
                }

                this.phraseCurrentlySaving = true;
                var selectedPhraseId = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].id;
                for (var j  = 0; j < this.textBlocks.length; j++) {
                    for (var i  = 0; i < this.textBlocks[j].phrases.length; i++) {
                        if (this.textBlocks[j].phrases[i].id == selectedPhraseId) {
                            this.textBlocks[j].phrases[i].translation = this.vocabBox.translationText;
                            this.textBlocks[j].phrases[i].reading = this.vocabBox.reading;
                        }
                    }
                }
                
                var saveData = {
                    words: this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].words,
                    reading: this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].reading,
                    translation: this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].translation,
                    lookup_count: this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].lookup_count,
                };

                if (this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].id == -1) {
                    saveData.stage = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage;
                } else {
                    saveData.id = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].id;
                }

                if (withStage) {
                    saveData.stage = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage;
                }

                axios.post('/vocabulary/phrase/save', saveData).then((response) => {
                    for (let j = 0; j < this.textBlocks.length; j++) {
                        for (let i = 0; i < this.textBlocks[j].phrases.length; i++) {
                            if (this.textBlocks[j].phrases[i].id == -1) {
                                this.textBlocks[j].phrases[i].id = parseInt(response.data);
                            }
                        }
                    }

                    this.phraseCurrentlySaving = false;
                }).catch((error) => {
                    console.log(error);
                }).then(() => {
                    
                });

                if (exampleSentenceChanged) {
                    this.updateExampleSentence();
                }
            },
            updatePhraseBorders() {
                for (var j  = 0; j < this.textBlocks.length; j++) {
                    for (var i = 0; i < this.textBlocks[j].words.length; i++) {
                        if (this.textBlocks[j].words[i].phraseIndexes.length) {
                            var lowestPhraseStage = 1000;
                            for (let p = 0; p < this.textBlocks[j].words[i].phraseIndexes.length; p++) {
                                if (parseInt(this.textBlocks[j].phrases[this.textBlocks[j].words[i].phraseIndexes[p]].stage) < lowestPhraseStage) {
                                    lowestPhraseStage = parseInt(this.textBlocks[j].phrases[this.textBlocks[j].words[i].phraseIndexes[p]].stage);
                                }
                            }

                            this.textBlocks[j].words[i].phraseStage = lowestPhraseStage;
                        }
                        
                        // phrase start
                        this.textBlocks[j].words[i].phraseStart = false;
                        this.textBlocks[j].words[i].phraseEnd = false;
                        if (this.textBlocks[j].words[i].phraseIndexes.length && (i == 0 || !this.textBlocks[j].words[i - 1].phraseIndexes.length)) {
                            this.textBlocks[j].words[i].phraseStart = true;
                        }
                        
                        // phrase end
                        if (this.textBlocks[j].words[i].phraseIndexes.length && (i + 1 == this.textBlocks[j].words.length || !this.textBlocks[j].words[i + 1].phraseIndexes.length)) {
                            this.textBlocks[j].words[i].phraseEnd = true;
                        }
                    }
                }
            },
            updateVocabBoxData(newVocabBoxData) {
                this.vocabBox.reading = newVocabBoxData.reading;
                this.vocabBox.baseWord = newVocabBoxData.baseWord;
                this.vocabBox.baseWordReading = newVocabBoxData.baseWordReading;
                this.vocabBox.phraseReading = newVocabBoxData.phraseReading;
                this.vocabBox.translationText = newVocabBoxData.translationText;
                
                this.$forceUpdate();
            },
            saveWord(withStage = false, exampleSentenceChanged = false) {
                var selectedWord = this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex];
                

                // update unique words in all blocks
                for (var j  = 0; j < this.textBlocks.length; j++) {
                    for (var i  = 0; i < this.textBlocks[j].uniqueWords.length; i++) {
                        if (this.textBlocks[j].uniqueWords[i].word.toLowerCase() == selectedWord.word.toLowerCase()) {
                            this.textBlocks[j].uniqueWords[i].translation = this.vocabBox.translationText;
                            this.textBlocks[j].uniqueWords[i].reading = this.vocabBox.reading;
                            this.textBlocks[j].uniqueWords[i].base_word = this.vocabBox.baseWord;
                            this.textBlocks[j].uniqueWords[i].base_word_reading = this.vocabBox.baseWordReading;
                            this.textBlocks[j].uniqueWords[i].stage = selectedWord.stage;
                        }
                    }
                }

                // update stages in all text
                for (var j  = 0; j < this.textBlocks.length; j++) {
                    for (var i  = 0; i < this.textBlocks[j].words.length; i++) {
                        if (this.textBlocks[j].words[i].word.toLowerCase() == selectedWord.word.toLowerCase()) {
                            this.textBlocks[j].words[i].stage = selectedWord.stage;
                        }
                    }
                }
                
                var saveData = {
                    id: selectedWord.id,
                    translation: this.vocabBox.translationText,
                    reading: this.vocabBox.reading,
                    base_word: this.vocabBox.baseWord,
                    base_word_reading: this.vocabBox.baseWordReading,
                    example_sentence: selectedWord.example_sentence,
                    lookup_count: selectedWord.lookup_count,
                };

                if (withStage) {
                    saveData.stage = selectedWord.stage;
                }

                axios.post('/vocabulary/word/save', saveData).then((response) => {
                }).catch(function (error) {
                    console.log(error);
                }).then(() => {

                });

                if (exampleSentenceChanged) {
                    this.updateExampleSentence();
                }

                this.$forceUpdate();
            },
            setStage(stage) {
                if (!this.selection.length || (this.selection.length > 1 && this.selectedPhrase === -1)) {
                    return;
                }

                // determine if saving is needed
                var save = 'none';
                if (this.selection.length == 1 && this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].stage !== stage) {
                    save = 'word';
                } else if (this.selection.length > 1 && this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage !== stage) {
                    save = 'phrase';
                }

                if (this.selectedPhrase == -1 && this.selection.length == 1) {
                    this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].stage = stage;
                    if (stage == 0) {
                        this.learnedWords ++;
                    }

                    // set stage for all words that match the selected word
                    for (var j  = 0; j < this.textBlocks.length; j++) {
                        for (var i  = 0; i < this.textBlocks[j].words.length; i++) {
                            if (this.textBlocks[j].words[i].word.toLowerCase() == this.selection[0].word.toLowerCase()) {
                                this.textBlocks[j].words[i].stage = stage;
                            }
                        }
                    }
                } else if (this.selectedPhrase !== -1) {
                    // set stage for all phrases that match the selected word
                    for (var j  = 0; j < this.textBlocks.length; j++) {
                        for (var i  = 0; i < this.textBlocks[j].phrases.length; i++) {
                            if (this.textBlocks[j].phrases[i].id == this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].id) {
                                this.textBlocks[j].phrases[i].stage = stage;
                            }
                        }
                    }
                    
                    this.updatePhraseBorders();
                }

                // add word/phrase to anki
                if (this.ankiAutoAddCards && stage < 0 && (this.vocabBox.stage >= 0 || this.vocabBox.stage === undefined)) {
                    this.addSelectedWordToAnki();
                }
                
                // save word/phrase
                this.updateSelectedWordStage();
                if (save == 'word') {
                    this.saveWord(true, stage < 0);

                } else if (save == 'phrase') {
                    this.savePhrase(true, stage < 0);
                }

                this.vocabBox.stage = stage;
            },
            updateSelectedWordStage() {
                if (this.selectedPhrase == -1 && this.selection.length) {
                    this.vocabBox.stage = parseInt(this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].stage);
                } else if (this.selectedPhrase !== -1){
                    this.vocabBox.stage = parseInt(this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage);
                }

                if (this.vocabBox.stage == 2) {
                    this.vocabBox.stage = undefined;
                }
            },
            getExampleSentence(withSpaces = false) {
                var sentenceIndexes = [];
                for (var i = 0; i < this.selection.length; i++) {
                    if (sentenceIndexes.indexOf(this.selection[i].sentence_index) == -1) {
                        sentenceIndexes.push(this.selection[i].sentence_index);
                    }
                }

                var exampleSentence = [];
                for (var i = 0; i < this.textBlocks[this.selectedTextBlock].words.length; i++) {
                    if (this.textBlocks[this.selectedTextBlock].words[i].word == 'NEWLINE' 
                        || sentenceIndexes.indexOf(this.textBlocks[this.selectedTextBlock].words[i].sentence_index) == -1) {
                        continue;
                    }

                    exampleSentence.push({
                        word: this.textBlocks[this.selectedTextBlock].words[i].word,
                        phrase_ids: []
                    });

                    if (withSpaces && this.textBlocks[this.selectedTextBlock].words[i].spaceAfter) {
                        exampleSentence[exampleSentence.length - 1].word += ' ';
                    }
                }

                return exampleSentence;
            },
            updateExampleSentence() {
                var exampleSentence = this.getExampleSentence();

                var targetType = this.selection.length > 1 ? 'phrase' : 'word';
                var targetId = this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].id;

                if (targetType == 'phrase') {
                    targetId = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].id;
                }

                axios.post('/vocabulary/save-example-sentence', {
                    targetType: targetType,
                    targetId: targetId,
                    exampleSentenceWords: JSON.stringify(exampleSentence),
                });
            },
            updateVocabBoxPositionDelay() {
                setTimeout(() => {
                    this.updateVocabBoxPosition();
                }, 200);
            },
            updateVocabBoxPosition() {
                var margin = 8;
                this.vocabBox.width = 400;
                var vocabBoxAreaElement = document.getElementsByClassName('vocab-box-area')[0];
                var vocabBoxArea = vocabBoxAreaElement.getBoundingClientRect();

                // update sidebar
                if (this.$props.vocabularySidebarFits && this.$props.vocabularySidebar) {
                    this.vocabBox.sidebarHidden = false;
                    this.vocabBox.height = vocabBoxAreaElement.offsetHeight;
                    this.vocabBox.positionLeft = vocabBoxArea.right;
                    this.vocabBox.positionTop = vocabBoxArea.top;
                    return;
                }

                if (!this.selection.length) {
                    return;
                }

                if (this.selection.length == 1) {
                    var selectedWordPositions = document.querySelector('[textblock="' + this.selectedTextBlock + '"] [wordindex="' + this.selection[0].wordIndex + '"]').getBoundingClientRect();
                } else if (this.selection.length > 1) {
                    var selectedWordPositions = document.querySelector('[textblock="' + this.selectedTextBlock + '"] [wordindex="' + this.selection[parseInt(this.selection.length / 2)].wordIndex + '"]').getBoundingClientRect();
                }

                this.vocabBox.positionLeft = selectedWordPositions.right - vocabBoxArea.left - this.vocabBox.width / 2 - (selectedWordPositions.right - selectedWordPositions.left) / 2;

                if (window.innerWidth  < 440) {
                    this.vocabBox.positionLeft = 0;
                } else if (this.vocabBox.positionLeft < margin) {
                    this.vocabBox.positionLeft = margin;
                } else if (this.vocabBox.positionLeft > vocabBoxArea.right - vocabBoxArea.left - this.vocabBox.width - margin) {
                    this.vocabBox.positionLeft = vocabBoxArea.right - vocabBoxArea.left - this.vocabBox.width - margin;
                }

                this.vocabBox.positionTop = selectedWordPositions.bottom - vocabBoxArea.top + vocabBoxAreaElement.scrollTop + 25;

                this.scrollToVocabBox();
            },
            scrollToVocabBox() {
                setTimeout(() => {
                    var vocabBox = document.getElementById('vocab-box');
                    if (vocabBox && this.$props.vocabBoxScrollIntoView == 'scroll-into-view') {
                        vocabBox.scrollIntoView(false);
                    }

                    if (vocabBox && this.$props.vocabBoxScrollIntoView == 'scroll-into-view-if-needed') {
                        vocabBox.scrollIntoViewIfNeeded(false);
                    }
                }, 450);
            },
            trimSearchTerm(searchTerm) {
                var trimmedSearchTerm = searchTerm;

                // norwegian
                if (this.$props.language == 'norwegian' && this.vocabBox.searchField.substring(0, 2) == 'å ') {
                    trimmedSearchTerm = this.vocabBox.searchField.slice(2);
                }

                if (this.$props.language == 'norwegian' && this.vocabBox.searchField.substring(0, 3) == 'et ') {
                    trimmedSearchTerm = this.vocabBox.searchField.slice(3);
                }

                if (this.$props.language == 'norwegian' && this.vocabBox.searchField.substring(0, 3) == 'en ') {
                    trimmedSearchTerm = this.vocabBox.searchField.slice(3);
                }

                if (this.$props.language == 'norwegian' && this.vocabBox.searchField.substring(0, 3) == 'ei ') {
                    trimmedSearchTerm = this.vocabBox.searchField.slice(3);
                }

                // german
                if (this.$props.language == 'german' && this.vocabBox.searchField.substring(0, 4) == 'die ') {
                    trimmedSearchTerm = this.vocabBox.searchField.slice(4);
                }

                if (this.$props.language == 'german' && this.vocabBox.searchField.substring(0, 4) == 'der ') {
                    trimmedSearchTerm = this.vocabBox.searchField.slice(4);
                }

                if (this.$props.language == 'german' && this.vocabBox.searchField.substring(0, 4) == 'das ') {
                    trimmedSearchTerm = this.vocabBox.searchField.slice(4);
                }

                return trimmedSearchTerm;
            }
        }
    }
</script>
