<template>
    <div
        :class="{
            'text-block-group': true,
            'plain-text-mode': plainTextMode,
            'w-100': true,
            'chinese-font': language == 'chinese'
        }"
    >
        <!-- Delete phrase dialog -->
        <delete-phrase-dialog 
            v-model="deletePhraseDialog"
            @confirm="deletePhrase"
        />

        <!-- Anki api notifications -->
        <v-snackbar
            v-for="(snackBar, snackBarIndex) in snackBars"
            :key="'snackbar-' + snackBarIndex"
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
            @mouseup.native.stop=";"
        >
            <div class="pl-3 pr-2 pt-1 d-flex font-weight-bold snackbar-title">
                <v-icon v-if="snackBar.type !== 'success' && snackBar.type !== 'update success'" color="error" class="mr-2">mdi-alert</v-icon>
                <v-icon v-else color="success" class="mr-2">mdi-cards</v-icon>

                <template v-if="snackBar.type =='error'">Anki error</template>
                <template v-if="snackBar.type =='success'">Added to anki</template>
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

        <!-- Text -->
        <div
            class="text-block"
            :style="{
                'font-size': fontSize + 'px',
            }"
            @mousedown.stop="startSelectionMouseEvent"
            @mousemove.stop="updateSelectionMouseEvent"
            @mouseup.stop="finishSelection"
            @touchstart="startSelectionTouchEvent"
            @touchmove="updateSelectionTouchEvent"
            @touchend="finishSelection"
            >
            
            <template v-for="(word, wordIndex) in words"><!--
                --><div class="subtitle-timestamp rounded-pill py-1 mt-12 mb-1" v-if="word.subtitleIndex !== -1"><!--
                    -->{{ subtitleTimestamps[word.subtitleIndex].start }}<!--
                --></div><!--
                --><br v-if="word.word === 'NEWLINE'" /><!--
                --><span
                    v-else
                    :wordindex="wordIndex"
                    :stage="word.stage"
                    :phrasestage="word.phraseStage"
                    :class="{
                        'no-highlight': hideAllHighlights || (hideNewWordHighlights && word.stage == 2),
                        'word': true,
                        'selected-font': true,
                        'highlighted': word.selected || word.hover,
                        'phrase': word.phraseIndexes.length,
                        'space-after': word.spaceAfter,
                        'phrase-start': word.phraseStart,
                        'phrase-end': word.phraseEnd,
                    }"
                    :style="{
                        'margin-bottom': (lineSpacing * 4) + 'px'
                    }"

                    :key="wordIndex"
                ><!--
                    --><template v-if="$props.language == 'japanese'"><!--
                        --><ruby class="rubyword selected-font" :wordindex="wordIndex"><!--
                            -->{{ word.word }}<!--
                            --><rt v-if="word.stage == 2 && furiganaOnNewWords && word.furigana.length && word.word !== word.furigana" :style="{'font-size': (fontSize - 4) + 'px'}"><!--
                                -->{{ word.furigana }}<!--
                            --></rt><!--
                            --><rt v-if="word.stage < 0 && furiganaOnHighlightedWords && word.furigana.length && word.word !== word.furigana" :style="{'font-size': (fontSize - 4) + 'px'}"><!--
                                -->{{ word.furigana }}<!--
                            --></rt><!--
                        --></ruby>
                    </template><!--
                    --><template v-else>{{ word.word }}</template><!--
                    --><template v-if="plainTextMode && word.spaceAfter">&nbsp;</template><!--
                --></span><!--
            --></template>
        </div>

        <!-- Vocabulary popup box -->
        <vocabulary-hover-box
            v-if="hoverVocabBox.active && !vocabBox.active && !hoverVocabBox.disabledWhileSelecting"
            ref="hoverVocabBox"
            :key="'hover-vocab-box' + hoverVocabBox.key"
            :user-translation="hoverVocabBox.userTranslation"
            :dictionary-translation="hoverVocabBox.dictionaryTranslation"
            :deepl-translation="hoverVocabBox.deeplTranslation"
            :positionLeft="hoverVocabBox.positionLeft"
            :positionTop="hoverVocabBox.positionTop"
            :arrowPosition="hoverVocabBox.arrowPosition"
            :reading="hoverVocabBox.reading"
            @update-position="updateHoverVocabularyBoxPosition"
        ></vocabulary-hover-box>

        <!-- Vocabulary popup box -->
        <vocabulary-box
            v-if="(!$props.vocabularySidebar || !$props.vocabularySidebarFits) && vocabBox.active && (!$props.vocabularyBottomSheet || !vocabBox.vocabularyBottomSheetVisible)"
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
            :inflections="vocabBox.inflections"
            :auto-highlight-words="$props.autoHighlightWords"
            :deepl-enabled="this.deeplEnabled"
            :textToSpeechAvailable="textToSpeechAvailable"
            :_reading="vocabBox.reading"
            :_baseWord="vocabBox.baseWord"
            :_baseWordReading="vocabBox.baseWordReading"
            :_phraseReading="vocabBox.phraseReading"
            :_translationText="vocabBox.translationText"
            :_searchField="vocabBox.searchField"
            @textToSpeech="textToSpeech"
            @setStage="setStage"
            @unselectAllWords="unselectAllWords"
            @updateVocabBoxData="updateVocabBoxData"
            @addNewPhrase="addNewPhrase"
            @deletePhrase="deletePhrase"
            @addSelectedWordToAnki="addSelectedWordToAnki"
        ></vocabulary-box>

        <!-- Vocabulary bottom sheet -->
        <v-bottom-sheet
            v-if="
                (!$props.vocabularySidebar || !$props.vocabularySidebarFits) 
                && vocabBox.active 
                && $props.vocabularyBottomSheet 
                && vocabBox.vocabularyBottomSheetVisible
            "
            v-model="vocabBox.active"
            persistent
            scrollable
        >
            <vocabulary-bottom-sheet
                :key="'vocab-bottom-sheet' + vocabBox.key"
                :language="$props.language"
                :active="vocabBox.active"
                :type="vocabBox.type"
                :kanjiList="vocabBox.kanjiList"
                :word="vocabBox.word"
                :phrase="vocabBox.phrase"
                :stage="vocabBox.stage"
                :inflections="vocabBox.inflections"
                :auto-highlight-words="$props.autoHighlightWords"
                :deepl-enabled="this.deeplEnabled"
                :textToSpeechAvailable="textToSpeechAvailable"
                :_reading="vocabBox.reading"
                :_baseWord="vocabBox.baseWord"
                :_baseWordReading="vocabBox.baseWordReading"
                :_phraseReading="vocabBox.phraseReading"
                :_translationText="vocabBox.translationText"
                :_searchField="vocabBox.searchField"
                @textToSpeech="textToSpeech"
                @setStage="setStage"
                @unselectAllWords="unselectAllWords"
                @updateVocabBoxData="updateVocabBoxData"
                @addNewPhrase="addNewPhrase"
                @showDeletePhraseDialog="showDeletePhraseDialog"
                @addSelectedWordToAnki="addSelectedWordToAnki"
            ></vocabulary-bottom-sheet>
        </v-bottom-sheet>

        <!--Vocabulary sidebar-->
        <vocabulary-side-box
            v-if="$props.vocabularySidebarFits && $props.vocabularySidebar && !vocabBox.sidebarHidden"
            ref="vocabularySideBox"
            :key="'vocab-side-box' + vocabBox.key"
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
            :inflections="vocabBox.inflections"
            :auto-highlight-words="$props.autoHighlightWords"
            :deepl-enabled="this.deeplEnabled"
            :textToSpeechAvailable="textToSpeechAvailable"
            :_reading="vocabBox.reading"
            :_baseWord="vocabBox.baseWord"
            :_baseWordReading="vocabBox.baseWordReading"
            :_phraseReading="vocabBox.phraseReading"
            :_translationText="vocabBox.translationText"
            :_searchField="vocabBox.searchField"
            @textToSpeech="textToSpeech"
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
    import TextToSpeechService from './../../services/TextToSpeechService';
    export default {
        data: function() {
            return {
                // dialogs
                deletePhraseDialog: false,

                // tts
                textToSpeechService: new TextToSpeechService(this.$props.language, this.$cookie, this.updateTextToSpeechState),
                textToSpeechAvailable: false,

                // text
                words: [],
                uniqueWords: JSON.parse(JSON.stringify(this.$props._text.uniqueWords)),
                uniqueWordMap: new Map(),
                phrases: JSON.parse(JSON.stringify(this.$props._text.phrases)),

                snackBars: [
                ],
                snackbarId: 1,
                ankiAutoAddCards: false,
                ankiShowNotifications: false,
                deeplEnabled: false,

                // hover vocabulary box
                hoverVocabBox: {
                    hoverVocabularyDelayTimeout: null,
                    dictionarySearchTerm: '',
                    disabledWhileSelecting: false,
                    active: false,
                    lastHoveredWordIndex: -1,
                    key: 0,
                    hoveredWords: null,
                    hoveredPhrase: -1,
                    reading: '',
                    userTranslation: '',
                    dictionaryTranslation: '',
                    deeplTranslation: '',
                    positionLeft: 0,
                    positionTop: 0,
                    arrowPosition: 'top',
                },

                // vocabulary box
                vocabBox: {
                    vocabularyBottomSheetVisible: false,
                    /*
                        This is required because sidebar is always visible, and it does not re-render
                        when active is changed.
                    */
                    key: 0,

                    /*
                        Keep the sidebar hidden until the first position
                        update, so it won't jump around on the screen when
                        a text is opened.
                    */
                    sidebarHidden: true,
                    active: false,

                    // inflections table
                    inflections: [],

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
                selectedPhrase: -1,
                phraseCurrentlySaving: false,

                // text selection
                phraseLengthLimit: 14,
                touchTimer: null,
                touchStartWordIndex: -1,
                selection: [],
                ongoingSelection: [],
                selectedPhrase: -1,
                ongoingSelectionStartingWordIndex: -1,
            }
        },
        props: {
            textType: {
                type: String,
                default: 'simple-text',
            },
            theme: String,
            fullscreen: Boolean,
            _text: Object,
            subtitleTimestamps: {
                type: Array,
                default: () => {
                    return [];
                }
            },
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
            vocabularyBottomSheet: {
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
            },
            vocabularyHoverBoxSearch: {
                type: Boolean,
                default: false
            },
            vocabularyHoverBoxDelay: {
                type: Number,
                default: 300
            },
            vocabularyHoverBoxPreferredPosition: {
                type: String,
                default: 'bottom'
            },
            vocabularyHoverBoxPositionCorrections: {
                type: Boolean,
                default: true,
            },
            autoHighlightWords: {
                type: Boolean,
                default: true
            }
        },
        mounted() {
            this.preProcessWords();
            window.addEventListener('resize', this.resizeHandle);
            window.addEventListener('mouseup', this.unselectAllWordsOnEmptyClick);
            window.addEventListener('keydown', this.hotkeyHandle);
            window.addEventListener('mousemove', this.closeHoverBox);

            axios.get('/settings/get-anki-settings').then((response) => {
                this.ankiAutoAddCards = response.data.ankiAutoAddCards;
                this.ankiShowNotifications = response.data.ankiShowNotifications;
            });

            axios.get('/dictionaries/deepl/is-enabled').then((response) => {
                this.deeplEnabled = response.data;
            });

            this.resizeHandle();
            this.updatePhraseBorders();
            this.updateTextToSpeechState();
        },
        beforeDestroy() {
            window.removeEventListener('resize', this.resizeHandle);
            window.removeEventListener('mouseup', this.unselectAllWordsOnEmptyClick);
            window.removeEventListener('keydown', this.hotkeyHandle);
            window.removeEventListener('mousemove', this.closeHoverBox);
        },
        methods: {
            textToSpeech() {
                if (!this.selection.length) {
                    return;
                }

                if (this.vocabBox.type === 'word') {
                    var text = this.vocabBox.reading.length ? this.vocabBox.reading : this.vocabBox.word;
                } else if (this.vocabBox.type !== 'word' && this.vocabBox.reading.length) {
                    var text = this.vocabBox.reading;
                } else {
                    var text = '';

                    this.vocabBox.phrase.forEach((phraseWord, index) => {
                        if (index) {
                            text += ' ';
                        }

                        text += phraseWord.word;
                    });
                }

                this.textToSpeechService.speak(text);
            },
            updateTextToSpeechState() {
                this.textToSpeechAvailable = this.textToSpeechService.getLanguageVoices().length > 0;
            },
            startSelectionTouchEvent: function(event) {
                var element = event.target
                if (event.target.localName === 'ruby') {
                    element = event.target.parentElement;
                }

                if (event.target.localName === 'rt') {
                    element = event.target.parentElement.parentElement;
                }

                if (!element.classList.contains('word')) {
                    this.unselectAllWords();
                    return;
                }

                if (this.$props.plainTextMode) {
                    return;
                }

                var wordIndex = parseInt(element.attributes['wordindex'].nodeValue);
                this.touchStartWordIndex = wordIndex;
                this.touchTimer = setTimeout(() => {
                    this.startSelection(wordIndex);
                }, 500);
            },
            startSelectionMouseEvent(event) {
                if (this.$props.plainTextMode) {
                    return;
                }

                this.startTime = performance.now();
                var element = event.target
                if (event.target.localName === 'ruby') {
                    element = event.target.parentElement;
                }

                if (event.target.localName === 'rt') {
                    element = event.target.parentElement.parentElement;
                }

                if (!element.classList.contains('word')) {
                    this.unselectAllWords();
                    return;
                }

                this.startSelection(parseInt(element.attributes['wordindex'].nodeValue));


            },
            updateSelectionTouchEvent: function(event) {
                if (!event.cancelable) {
                    if (this.touchTimer) {
                        clearTimeout(this.touchTimer);
                        this.touchTimer = null;
                    }
                    
                    return;
                }
                
                if (this.ongoingSelection.length) {
                    event.preventDefault();
                }
                
                var touch = event.changedTouches[0];
                var element = document.elementFromPoint( touch.clientX, touch.clientY );

                var wordIndex = null;
                if (element !== null && element.classList.contains('word') || element.classList.contains('rubyword')) {
                    wordIndex = element.getAttribute('wordindex');
                }

                if (this.touchTimer) {
                    if ((wordIndex === null || parseInt(wordIndex) !== this.touchStartWordIndex)) {
                        clearTimeout(this.touchTimer);
                        this.touchTimer = null;
                    }

                    return;
                }

                if (wordIndex !== null && this.ongoingSelection.length) {
                    this.updateSelection(wordIndex);
                }
            },
            updateSelectionMouseEvent(event) {
                var element = event.target;
                var wordIndex = -1;
                if (event.target.localName === 'ruby') {
                    element = event.target.parentElement;
                }

                if (element.classList.contains('word')) {
                    wordIndex = parseInt(element.attributes['wordindex'].nodeValue);
                }

                if (wordIndex === -1) {
                    if (wordIndex !== this.hoverVocabBox.lastHoveredWordIndex) {
                        this.closeHoverBox();
                        this.removePhraseHover();
                    }

                    return;
                }
                
                if (event.buttons === 0 && wordIndex === -1 || wordIndex !== this.hoverVocabBox.lastHoveredWordIndex) {
                    this.removePhraseHover();
                }

                if (wordIndex === -1) {
                    return;
                }

                if (event.buttons === 0 && wordIndex !== this.hoverVocabBox.lastHoveredWordIndex) {
                    this.updateHoverSelection(wordIndex);
                }

                if (!this.ongoingSelection.length) {
                }

                if (!event.buttons !== 1) {
                }

                if (this.ongoingSelection.length && event.buttons === 1) {
                    this.updateSelection(wordIndex);
                }

            },
            startSelection: function(wordIndex) {
                if (this.$props.plainTextMode) {
                    return;
                }

                // update vocab box
                this.hoverVocabBox.disabledWhileSelecting = true;
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
                this.touchTimer = null;

                if (this.ongoingSelection.length == 1 && this.ongoingSelection[0].wordIndex == wordIndex) {
                    return;
                }

                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                }

                // set selected word
                var selectedWord = {
                    word: this.words[wordIndex].word,
                    spaceAfter: this.words[wordIndex].spaceAfter,
                    wordIndex: wordIndex,
                    sentence_index: this.words[wordIndex].sentence_index
                };


                this.ongoingSelection = [selectedWord];
                this.words[wordIndex].selected = true;

                this.ongoingSelectionStartingWordIndex = wordIndex;

            },
            updateSelection(wordIndex) {
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
                    firstWordIndex = wordIndex;
                    lastWordIndex = this.ongoingSelectionStartingWordIndex;
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

                if (!this.ongoingSelection.length) {
                }

            },
            finishSelection: function() {
                if (this.touchTimer) {
                    clearTimeout(this.touchTimer);
                    this.touchTimer = null;
                    return;
                }

                this.selectionOngoing = false;
                if (this.ongoingSelection.length == 1) {
                    // if the selected word is in an phrase, select the phrase instead
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
                    var uniqueWordIndex = this.uniqueWordMap.get(this.ongoingSelection[i].word.toLowerCase());
                    this.ongoingSelection[i].uniqueWordIndex = uniqueWordIndex;
                    this.ongoingSelection[i].reading = this.uniqueWords[uniqueWordIndex].reading;
                    this.ongoingSelection[i].kanji = this.uniqueWords[uniqueWordIndex].kanji;
                }

                this.selection = this.ongoingSelection;
                this.vocabBox.inflections = [];
                this.ongoingSelection = [];

                if (this.selection.length) {
                    this.selectedPhrase = this.getSelectedPhraseIndex();

                    // update lookup counts
                    if (this.selection.length == 1) {
                        var inflectionSearchTerm = this.uniqueWords[uniqueWordIndex].base_word.length ? this.uniqueWords[uniqueWordIndex].base_word : this.uniqueWords[uniqueWordIndex].word;
                        this.requestInflections(inflectionSearchTerm);
                        this.updateWordLookupCount(this.selection[0].word);
                    } else if (this.selectedPhrase !== -1) {
                        this.updatePhraseLookupCount(this.selectedPhrase);
                    }

                    this.updatePhraseBorders();
                    this.updateVocabBoxDataAfterSelection();
                }
            },
            requestInflections: function(term) {
                if (this.$props.language !== 'japanese') {
                    return;
                }

                // search inflections
                axios.post('/dictionaries/search/inflections', {
                    term: term
                }).then((response) => {
                    if (response.data === '[]' || response.data == '') {
                        return;
                    }

                    var data = JSON.parse(response.data);
                    var displayedInflections = ['Non-past', 'Non-past, polite', 'Past', 'Past, polite', 'Te-form', 'Potential', 'Passive', 'Causative', 'Causative Passive', 'Imperative'];
                    
                    for (var i = 0; i < data.length; i++) {
                        if (!displayedInflections.includes(data[i].name)) {
                            continue;
                        }

                        var index = this.vocabBox.inflections.findIndex(item => item.name === data[i].name);
                        if (index == -1) {
                            this.vocabBox.inflections.push({
                                name: data[i].name,
                            });
                            index = this.vocabBox.inflections.length - 1;
                        }
                        // add different forms to the item
                        if (data[i].form == 'aff-plain:') {
                            this.vocabBox.inflections[index].affPlain = data[i].value;
                        }
                        if (data[i].form == 'aff-formal:') {
                            this.vocabBox.inflections[index].affFormal = data[i].value;
                        }
                        if (data[i].form == 'neg-plain:') {
                            this.vocabBox.inflections[index].negPlain = data[i].value;
                        }
                        if (data[i].form == 'neg-formal:') {
                            this.vocabBox.inflections[index].negFormal = data[i].value;
                        }
                    }
                });
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
                        var uniqueWordIndex = this.uniqueWordMap.get(this.words[currentWordIndex].word.toLowerCase());
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
            updateHoverSelection: function(wordIndex) {
                this.closeHoverBox();

                var hoveredWords = [];
                var hoveredPhraseIndex = -1;
                this.hoverVocabBox.lastHoveredWordIndex = wordIndex;

                var phraseIndexes = this.words[wordIndex].phraseIndexes;
                if (!phraseIndexes.length) {

                    // update hovered words
                    var word = JSON.parse(JSON.stringify(this.words[wordIndex]));
                    word.hover = true;
                    hoveredWords.push(word);
                    hoveredWords[0].wordIndex = wordIndex;
                    this.showHoverVocabBox(hoveredWords);

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

                this.showHoverVocabBox(hoveredWords, hoveredPhraseIndex);
            },
            showHoverVocabBox: function(hoveredWords, hoveredPhraseIndex) {
                var data = {
                    hoveredWords: JSON.parse(JSON.stringify(hoveredWords)),
                    translation: '',
                    reading: '',
                };

                if (hoveredWords !== null && hoveredWords.length === 1) {
                    var uniqueWordIndex = this.uniqueWordMap.get(hoveredWords[0].word.toLowerCase());
                    var uniqueWord = this.uniqueWords[uniqueWordIndex];

                    data.translation = uniqueWord.translation;
                    data.reading = uniqueWord.reading;
                    data.hoveredWords[0].lemma = uniqueWord.base_word;
                }

                if (hoveredWords !== null && hoveredWords.length > 1) {
                    data.translation = this.phrases[hoveredPhraseIndex].translation;
                    data.reading = this.phrases[hoveredPhraseIndex].reading;
                }

                this.updateHoverVocabularyBox(data);
            },
            updateHoverVocabularyBox(data) {
                if (!this.$props.vocabularyHoverBox || this.$props.plainTextMode || data.hoveredWords === null) {
                    this.closeHoverBox();
                    return;
                } else {
                    this.hoverVocabBox.hoveredWords = data.hoveredWords;
                    this.hoverVocabBox.hoveredPhrase = data.hoveredPhrase;
                    this.hoverVocabBox.userTranslation = data.translation;
                    this.hoverVocabBox.dictionaryTranslation = 'loading';
                    this.hoverVocabBox.deeplTranslation = this.deeplEnabled ? 'loading' : 'deepl-disabled';
                    this.hoverVocabBox.reading = data.reading;

                    // clear previous delay timeout
                    if (this.hoverVocabBox.hoverVocabularyDelayTimeout !== null) {
                        this.clearHoverVocabularyBoxTimeout();
                    }

                    // check if dictionary search option is enabled
                    if (!this.$props.vocabularyHoverBoxSearch) {
                        this.hoverVocabBox.hoverVocabularyDelayTimeout = setTimeout(() => {
                            this.hoverVocabBox.dictionaryTranslation = 'dictionary-search-disabled';
                            this.hoverVocabBox.deeplTranslation = '';
                            this.hoverVocabBox.active = true;
                            this.$nextTick(() => {
                                this.updateHoverVocabularyBoxPosition();
                            });
                        }, this.$props.vocabularyHoverBoxDelay);
                        return;
                    }

                    // call the hover vocabulary search function with a delay
                    this.hoverVocabBox.hoverVocabularyDelayTimeout = setTimeout(() => {
                        this.hoverVocabBox.active = true;
                        this.$nextTick(() => {
                            this.updateHoverVocabularyBoxPosition();
                        });

                        if (data.hoveredWords.length === 1) {
                            var term = data.hoveredWords[0].word;
                            if (data.hoveredWords[0].lemma.length) {
                                term = this.trimSearchTerm(data.hoveredWords[0].lemma);
                            }
                        } else {

                            // build search term for phrases, and adding spaces
                            var term = '';
                            for (let i = 0; i < data.hoveredWords.length; i++) {
                                term += data.hoveredWords[i].word;

                                if (data.hoveredWords[i].spaceAfter && i < data.hoveredWords.length - 1) {
                                    term += ' ';
                                }
                            }

                            data.hoveredWords.map(hoveredWord => hoveredWord.word).join('');
                        }


                        this.makeHoverVocabularyBoxSearchRequest(term);
                    }, this.$props.vocabularyHoverBoxDelay);
                }
            },
            updateHoverVocabularyBoxPosition() {
                var hoverVocabBoxElement = document.getElementById('vocab-hover-box');
                if (hoverVocabBoxElement === null) {
                    return;
                }

                var margin = 8;
                var hoverVocabBoxWidth = 300;
                var hoverVocabBoxHeight = hoverVocabBoxElement.getBoundingClientRect().height;
                var vocabBoxAreaElement = document.getElementsByClassName('vocab-box-area')[0];
                var vocabBoxArea = vocabBoxAreaElement.getBoundingClientRect();


                if (this.hoverVocabBox.hoveredWords.length == 1) {
                    var hoveredWordPositions = document.querySelector('[wordindex="' + this.hoverVocabBox.hoveredWords[0].wordIndex + '"]').getBoundingClientRect();
                } else {
                    var hoveredWordPositions = document.querySelector('[wordindex="' + this.hoverVocabBox.hoveredWords[parseInt(this.hoverVocabBox.hoveredWords.length / 2)].wordIndex + '"]').getBoundingClientRect();
                }

                var hoveredWordPositions = document.querySelector('[wordindex="' + this.hoverVocabBox.hoveredWords[0].wordIndex + '"]').getBoundingClientRect();

                // set horizontal position
                this.hoverVocabBox.positionLeft = hoveredWordPositions.right - vocabBoxArea.left - hoverVocabBoxWidth / 2 - (hoveredWordPositions.right - hoveredWordPositions.left) / 2;
                if (this.hoverVocabBox.positionLeft < margin) {
                    this.hoverVocabBox.positionLeft = margin;
                } else if (this.hoverVocabBox.positionLeft > vocabBoxArea.right - vocabBoxArea.left - hoverVocabBoxWidth - margin) {
                    this.hoverVocabBox.positionLeft = vocabBoxArea.right - vocabBoxArea.left - hoverVocabBoxWidth - margin;
                }

                // set vertical position
                
                // set preferred location
                this.hoverVocabBox.arrowPosition = this.$props.vocabularyHoverBoxPreferredPosition;

                // correct preferred location based on available space
                
                /*
                    Is there enough space on the bottom? If not, move the hover box to the top.
                    
                    There is a special case, when there is not enough space on the bottom, however the top half of the screen is smaller
                    than the bottom one. However, overflow by the hover box on the top does not affect the scrollbar, while on the bottom it
                    does, so it won't be corrected.
                */
                if (
                    this.$props.vocabularyHoverBoxPositionCorrections && 
                    this.hoverVocabBox.arrowPosition == 'bottom' && 
                    (vocabBoxArea.height + vocabBoxAreaElement.scrollTop) - (hoveredWordPositions.bottom - vocabBoxArea.top + vocabBoxAreaElement.scrollTop + 25) < hoverVocabBoxHeight
                ) {
                    this.hoverVocabBox.arrowPosition = 'top';
                }

                /*
                    Is there enough space on the top?
                */
                if (
                    this.$props.vocabularyHoverBoxPositionCorrections && 
                    this.hoverVocabBox.arrowPosition == 'top' && 
                    hoveredWordPositions.top - 25 - 30 < hoverVocabBoxHeight
                ) {
                    /*
                        If there's not enuogh space on the top, move the hover box to the bottom, but only if there's enough space on the bottom,
                        otherwise prefer to use the top position, because that does not cause scroll issues.
                    */
                    if ((vocabBoxArea.height + vocabBoxAreaElement.scrollTop) - (hoveredWordPositions.bottom - vocabBoxArea.top + vocabBoxAreaElement.scrollTop + 25) >= hoverVocabBoxHeight) {
                        this.hoverVocabBox.arrowPosition = 'bottom';
                    }
                }

                // set hover vocabulary box's location based on preference and correction
                if (this.hoverVocabBox.arrowPosition == 'top') {
                    this.hoverVocabBox.positionTop = hoveredWordPositions.top - vocabBoxArea.top + vocabBoxAreaElement.scrollTop - hoverVocabBoxHeight - 25;
                } else {
                    this.hoverVocabBox.positionTop = hoveredWordPositions.bottom - vocabBoxArea.top + vocabBoxAreaElement.scrollTop + 25;
                }

                
            },
            removePhraseHover: function() {
                for (let i  = 0; i < this.words.length; i++) {
                    this.words[i].hover = false;
                }
            },
            closeHoverBox() {
                this.clearHoverVocabularyBoxTimeout();
                this.hoverVocabBox.lastHoveredWordIndex = -1;
                this.hoverVocabBox.dictionarySearchTerm = '';
                this.hoverVocabBox.hoveredWords = null;
                this.hoverVocabBox.active = false;
                this.hoverVocabBox.positionLeft = 0;
                this.hoverVocabBox.positionTop = 0;
                this.hoverVocabBox.userTranslation = '';
                this.hoverVocabBox.dictionaryTranslation = '';
                this.hoverVocabBox.deeplTranslation = '';
                this.hoverVocabBox.reading = '';
                this.hoverVocabBox.hoveredPhrase = -1;
                this.hoverVocabBox.key ++;
            },
            preProcessWords() {
                for (let i = 0; i < this.uniqueWords.length; i++) {
                    this.uniqueWordMap.set(this.uniqueWords[i].word, i);
                }

                for (let i = 0; i < this.$props._text.words.length; i++) {
                    // skip whitespace
                    if (/\S/.test(this.$props._text.words[i].word) === false) {
                        continue;
                    }

                    this.words.push(this.$props._text.words[i]);
                }
            },
            hotkeyHandle(event) {
                if (!this.$props.hotkeysEnabled) {
                    return;
                }

                switch(event.which) {
                    // set level to new
                    case 86:
                        if (!event.ctrlKey) {
                            this.textToSpeech();
                        }
                        
                        break;

                    // set level to new
                    case 67:
                        if (!event.ctrlKey) {
                            this.setStage(2);
                        }
                        
                        break;

                    // set level 0-7
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

                    // set level 0-7 numpad
                    case 96:
                    case 97:
                    case 98:
                    case 99:
                    case 100:
                    case 101:
                    case 102:
                    case 103:
                        event.preventDefault();
                        this.setStage(96 - event.which);
                        break;

                    // set level to ignore
                    case 88:
                        event.preventDefault();
                        this.setStage(1);
                        break;

                        // decrease font size
                    case 73:
                        // do not do anything if ctrl+shift+i is pressed for dev tools
                        if (event.ctrlKey || event.shiftKey) {
                            return;
                        }

                        this.$emit('decrease-font-size');
                        break;

                    // increase font size
                    case 79:
                        event.preventDefault();
                        this.$emit('increase-font-size');
                        break;

                    // scroll up
                    case 38:
                    case 87:
                        event.preventDefault();
                        this.scrollText('up', event.ctrlKey || event.shiftKey);
                        break;

                    // scroll down
                    case 40:
                    case 83:
                        event.preventDefault();
                        this.scrollText('down', event.ctrlKey || event.shiftKey);
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
                    var currentWordIndex = this.words.length - 1;
                } else {
                    var currentWordIndex = this.selection[0].wordIndex;
                }

                var wordToSelect = -1;

                // there are no previous words
                if (currentWordIndex == 0) {
                    return;
                }

                // go through the text backwards, and find a word to select
                for (var wordIndex = currentWordIndex - 1; wordIndex >= 0; wordIndex--) {
                    // skip not displayed whitespace words
                    if (document.querySelector('.word[wordindex="' + wordIndex  + '"]') === null) {
                        continue;
                    }

                    // select the previous word if it's a simple arrow key press
                    if (!newWordOnly && !highlightedWordOnly) {
                        wordToSelect = wordIndex;
                        break;
                    }

                    // select the previous new word
                    if (newWordOnly && this.words[wordIndex].stage == 2) {
                        wordToSelect = wordIndex;
                        break;
                    }

                    // select the previous highlighted word
                    if (highlightedWordOnly && this.words[wordIndex].stage < 0) {
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
                    this.startSelection(wordToSelect);
                    this.finishSelection();;
                });
            },
            selectNextWord(newWordOnly, highlightedWordOnly) {
                if (!this.selection.length) {
                    var currentWordIndex = 0;
                } else {
                    var currentWordIndex = this.selection[this.selection.length - 1].wordIndex;
                }

                var wordToSelect = -1;

                // there are no next words to select
                if (currentWordIndex == this.words.length - 1) {
                    return;
                }

                // go through the text forward, and find a word to select
                for (var wordIndex = currentWordIndex + 1; wordIndex < this.words.length; wordIndex++) {
                    // skip not displayed whitespace words
                    if (document.querySelector('.word[wordindex="' + wordIndex  + '"]') === null) {
                        continue;
                    }

                    // select the previous word if it's a simple arrow key press
                    if (!newWordOnly && !highlightedWordOnly) {
                        wordToSelect = wordIndex;
                        break;
                    }

                    // select the previous new word
                    if (newWordOnly && this.words[wordIndex].stage == 2) {
                        wordToSelect = wordIndex;
                        break;
                    }

                    // select the previous highlighted word
                    if (highlightedWordOnly && this.words[wordIndex].stage < 0) {
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
                    this.startSelection(wordToSelect);
                    this.finishSelection();;
                });
            },
            scrollText(direction, largeScroll) {
                let scrollChange = direction == 'up' ? -40 : 40;

                if (largeScroll) {
                    scrollChange *= 8;
                }


                document.getElementsByClassName('vocab-box-area')[0].scrollBy(0, scrollChange);
            },
            updateVocabBoxDataAfterSelection() {
                this.vocabBox.tab = 0;
                this.vocabBox.active = true;

                // update vocab box data
                this.vocabBox.searchField = '';
                this.vocabBox.translationText = '';
                this.vocabBox.word = '';
                this.vocabBox.phrase = [];
                this.vocabBox.reading = '';
                this.vocabBox.kanjiList = [];
                this.vocabBox.baseWord = '';
                this.vocabBox.baseWordReading = '';

                if (this.selection.length == 1) {
                    var uniqueWord = this.uniqueWords[this.selection[0].uniqueWordIndex];
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
                        this.vocabBox.reading = this.phrases[this.selectedPhrase].reading;
                        this.vocabBox.translationText = this.phrases[this.selectedPhrase].translation;
                        this.vocabBox.stage = this.phrases[this.selectedPhrase].stage;
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

                this.vocabBox.key ++;
                this.resizeHandle();
                this.hoverVocabBox.disabledWhileSelecting = false;
            },
            clearHoverVocabularyBoxTimeout() {
                if (this.hoverVocabBox.hoverVocabularyDelayTimeout === null) {
                    return;
                }

                clearTimeout(this.hoverVocabBox.hoverVocabularyDelayTimeout);
                this.hoverVocabBox.hoverVocabularyDelayTimeout = null;
            },
            makeHoverVocabularyBoxSearchRequest(term) {
                if (!this.$props.vocabularyHoverBoxSearch) {
                    this.hoverVocabBox.dictionaryTranslation = '';
                    this.hoverVocabBox.deeplTranslation = '';
                }

                // do not make a search request if a word has been selected 
                if (this.selection.length) {
                    return;
                }

                // do not make search request for empty string
                if (term === '') {
                    return;
                }

                term = term.toLowerCase();
                this.hoverVocabBox.dictionarySearchTerm = term;


                // make dictionary search
                axios.post('/dictionaries/search-for-hover-vocabulary', {
                    language: this.$props.language,
                    term: term
                }).then((response) => {
                    // return if a different word has been selected
                    // after the request was sent
                    if (this.hoverVocabBox.dictionarySearchTerm !== response.data.term) {
                        return;
                    }

                    // return if there is no word selected anymore
                    if (this.hoverVocabBox.dictionarySearchTerm === '') {
                        return;
                    }

                    this.hoverVocabBox.dictionaryTranslation = response.data.definitions.join(';');
                    this.hoverVocabBox.key ++;
                    this.$nextTick(() => {
                        this.updateHoverVocabularyBoxPosition();
                    });
                });

                // make deepl search
                if (this.deeplEnabled) {
                    axios.post('/dictionaries/deepl/search', {
                        language: this.$props.language,
                        term: term
                    }).then((response) => {
                        // return if a different word has been selected
                        // after the request was sent
                        if (this.hoverVocabBox.dictionarySearchTerm !== response.data.term) {
                            return;
                        }

                        // return if there is no word selected anymore
                        if (this.hoverVocabBox.dictionarySearchTerm === '') {
                            return;
                        }

                        this.hoverVocabBox.deeplTranslation = response.data.definitions;
                        this.hoverVocabBox.key ++;
                        this.$nextTick(() => {
                            this.updateHoverVocabularyBoxPosition();
                        }); 
                    }).catch(() => {
                        this.hoverVocabBox.deeplTranslation = 'DeepL error';
                    });
                }
            },
            unselectAllWordsOnEmptyClick(event) {
                if (event.target.classList.contains('v-overlay__scrim')) {
                    return;
                }

                this.unselectAllWords();
            },
            unselectAllWords() {
                if (this.selection.length == 1) {
                    this.saveWord();
                } else if (this.selectedPhrase !== -1) {
                    this.savePhrase();
                }

                this.selectedPhrase = -1;
                this.selection = [];
                this.vocabBox.active = false;

                this.unselectAllWordsProcess();
                this.removePhraseHover();
                this.hoverVocabBox.disabledWhileSelecting = false;
            },
            unselectAllWordsProcess() {
                this.selectedPhrase = -1;
                this.selection = [];
                this.vocabBox.active = false;
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

                for(let i = 0; i < this.words.length; i++) {
                    this.words[i].selected = false;
                }
            },
            updateWordLookupCount(word) {
                let uniqueWordIndex = this.uniqueWordMap.get(word.toLowerCase());

                this.uniqueWords[uniqueWordIndex].lookup_count ++;
                this.uniqueWords[uniqueWordIndex].definitions_checked  = true;
                for (var i  = 0; i < this.words.length; i++) {
                    if (this.words[i].word.toLowerCase() == word) {
                        this.words[i].lookup_count ++;
                    }
                }
            },
            updatePhraseLookupCount(phraseIndex) {
                this.phrases[phraseIndex].lookup_count ++;
                this.phrases[phraseIndex].definitions_checked  = true;
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
                        word: this.uniqueWords[this.selection[0].uniqueWordIndex].word,
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

                axios.post('/anki/add-card', data).catch((error) => {
                        if (!this.ankiShowNotifications) {
                            return;
                        }

                        this.snackBars.push({id: this.snackbarId, content: data.word + ': ' + error.response.data.message, type: 'error'});
                        var snackbarToRemove = this.snackbarId;
                        this.snackbarId ++;
                        setTimeout(() => {
                            this.removeSnackbar(snackbarToRemove);
                        }, 5000);
                }).then((response) => {
                    if (response.status !== 200) {
                         return;
                    }

                    if (!this.ankiShowNotifications) {
                        return;
                    }

                    this.snackBars.push({id: this.snackbarId, content: data.word, type: response.data});

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
                    definitions_checked: true,
                };

                for (var i = 0; i < this.selection.length; i++) {
                    if (this.selection[i].word.toLowerCase() == 'newline') {
                        continue;
                    }

                    phrase.words.push(this.selection[i].word.toLowerCase());
                }

                // find all instance of the new phrase in the text
                var phraseOccurences = [];
                for (var i = 0; i < this.words.length; i++) {
                    // check if the current word is the start of the phrase
                    if (this.words[i].word.toLowerCase() == phrase.words[0]) {
                        phraseOccurences.push([
                            {
                                word: this.words[i].word.toLowerCase(),
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

                        if (phrase.words[phraseOccurences[p].length] == this.words[i].word.toLowerCase() &&
                            (i - 1) == phraseOccurences[p][phraseOccurences[p].length - 1].wordIndex + phraseOccurences[p][phraseOccurences[p].length - 1].newLineCount) {
                            phraseOccurences[p].push({
                                word: this.words[i].word.toLowerCase(),
                                wordIndex: i,
                                newLineCount: 0
                            });
                        }

                        // count 'NEWLINE' words for comparison
                        if (this.words[i].word.toLowerCase() == 'newline') {
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
                        this.words[phraseOccurences[p][i].wordIndex].phraseIndexes.push(this.phrases.length);
                    }
                }

                this.phrases.push(JSON.parse(JSON.stringify(phrase)));

                this.updatePhraseBorders();
                this.selectedPhrase = this.getSelectedPhraseIndex();

                this.updateSelectedWordStage();
                this.resizeHandle();
                this.savePhrase();
                this.vocabBox.type = 'phrase';
            },
            getSelectedPhraseIndex() {
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
            showDeletePhraseDialog() {
                this.deletePhraseDialog = true;
            },
            deletePhrase() {
                if (this.selectedPhrase == -1) {
                    return;
                }
                
                this.deletePhraseDialog = false;
                var deletedPhraseId = this.phrases[this.selectedPhrase].id;
                var deletedPhraseIndex = this.phrases.map(e => e.id).indexOf(deletedPhraseId);

                for (var i  = 0; i < this.words.length; i++) {
                    // remove phrase index from words
                    for (var p = this.words[i].phraseIndexes.length - 1; p >= 0; p--) {
                        if (this.words[i].phraseIndexes[p] == deletedPhraseIndex) {
                            this.words[i].phraseIndexes.splice(p, 1);
                            break;
                        }
                    }

                    // decrease phrase indexes larger than the deleted one
                    for (var p = this.words[i].phraseIndexes.length - 1; p >= 0; p--) {
                        if (this.words[i].phraseIndexes[p] > deletedPhraseIndex) {
                            this.words[i].phraseIndexes[p] --;
                        }
                    }
                }

                // delete phrase
                this.phrases.splice(deletedPhraseIndex, 1);

                axios.post('/vocabulary/phrases/delete', {
                    phraseId: deletedPhraseId
                }).then(function (response) {
                });


                this.selectedPhrase = -1;
                this.selection = [];
                this.removePhraseHover();
                this.unselectAllWords();
                this.updatePhraseBorders();
            },
            savePhrase(withStage = false, exampleSentenceChanged = false) {
                if (this.phraseCurrentlySaving) {
                    return;
                }

                this.phraseCurrentlySaving = true;
                var selectedPhraseId = this.phrases[this.selectedPhrase].id;
                for (var i  = 0; i < this.phrases.length; i++) {
                    if (this.phrases[i].id == selectedPhraseId) {
                        this.phrases[i].translation = this.vocabBox.translationText;
                        this.phrases[i].reading = this.vocabBox.reading;
                    }
                }

                var url = '/vocabulary/phrases/update';
                var saveData = {
                    reading: this.phrases[this.selectedPhrase].reading,
                    translation: this.phrases[this.selectedPhrase].translation,
                    lookup_count: this.phrases[this.selectedPhrase].lookup_count,
                };

                if (this.phrases[this.selectedPhrase].id === -1) {
                    saveData.words = JSON.stringify(this.phrases[this.selectedPhrase].words);
                    saveData.stage = this.phrases[this.selectedPhrase].stage;
                    url = '/vocabulary/phrases/create';
                } else {
                    saveData.id = this.phrases[this.selectedPhrase].id;
                }

                if (withStage) {
                    saveData.stage = this.phrases[this.selectedPhrase].stage;
                }

                axios.post(url, saveData).then((response) => {
                    for (let i = 0; i < this.phrases.length; i++) {
                        if (this.phrases[i].id == -1) {
                            this.phrases[i].id = parseInt(response.data);
                        }
                    }

                    this.phraseCurrentlySaving = false;
                }).catch((error) => {
                });

                if (exampleSentenceChanged) {
                    this.updateExampleSentence();
                }
            },
            updatePhraseBorders() {
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
            updateVocabBoxData(newVocabBoxData) {
                this.vocabBox.reading = newVocabBoxData.reading;
                this.vocabBox.baseWord = newVocabBoxData.baseWord;
                this.vocabBox.baseWordReading = newVocabBoxData.baseWordReading;
                this.vocabBox.phraseReading = newVocabBoxData.phraseReading;
                this.vocabBox.translationText = newVocabBoxData.translationText;
            },
            saveWord(withStage = false, exampleSentenceChanged = false) {
                var selectedWord = this.uniqueWords[this.selection[0].uniqueWordIndex];


                // update unique words in all blocks
                for (var i  = 0; i < this.uniqueWords.length; i++) {
                    if (this.uniqueWords[i].word.toLowerCase() == selectedWord.word.toLowerCase()) {
                        this.uniqueWords[i].translation = this.vocabBox.translationText;
                        this.uniqueWords[i].reading = this.vocabBox.reading;
                        this.uniqueWords[i].base_word = this.vocabBox.baseWord;
                        this.uniqueWords[i].base_word_reading = this.vocabBox.baseWordReading;
                        this.uniqueWords[i].stage = selectedWord.stage;
                    }
                }

                // update stages in all text
                for (var i  = 0; i < this.words.length; i++) {
                    if (this.words[i].word.toLowerCase() == selectedWord.word.toLowerCase()) {
                        this.words[i].stage = selectedWord.stage;
                        this.words[i].furigana = this.vocabBox.reading;
                    }
                }

                var saveData = {
                    id: selectedWord.id,
                    translation: this.vocabBox.translationText,
                    reading: this.vocabBox.reading,
                    base_word: this.vocabBox.baseWord,
                    base_word_reading: this.vocabBox.baseWordReading,
                    lookup_count: selectedWord.lookup_count,
                };

                if (withStage) {
                    saveData.stage = selectedWord.stage;
                }

                axios.post('/vocabulary/word/update', saveData).catch(function (error) {
                });

                if (exampleSentenceChanged) {
                    this.updateExampleSentence();
                }
            },
            setStage(stage) {
                var hoverSetStage = false;

                // do not set selected phrases to ignored
                if (this.selection.length > 1 && stage > 0) {
                    return;
                }

                if (!this.selection.length && this.hoverVocabBox.hoveredWords !== null) {
                    hoverSetStage = true;

                    // do not set hovered phrases to ignored
                    if (this.hoverVocabBox.hoveredWords.length > 1 && stage > 0) {
                        return;
                    }

                    // select hovered word and click on it
                    for (let i = 0; i < this.hoverVocabBox.hoveredWords.length; i++) {
                        if (!this.hoverVocabBox.hoveredWords[i].hover) {
                            continue;
                        }

                        this.startSelection(this.hoverVocabBox.hoveredWords[0].wordIndex);
                        this.finishSelection();
                        break;
                    }
                }

                if (!this.selection.length || (this.selection.length > 1 && this.selectedPhrase === -1)) {
                    return;
                }

                // determine if saving is needed
                var save = 'none';
                if (this.selection.length == 1 && this.uniqueWords[this.selection[0].uniqueWordIndex].stage !== stage) {
                    save = 'word';
                } else if (this.selection.length > 1 && this.phrases[this.selectedPhrase].stage !== stage) {
                    save = 'phrase';
                }

                if (this.selectedPhrase == -1 && this.selection.length == 1) {
                    if (stage == 0) {
                        this.learnedWords ++;
                    }

                    // set stage for all unique words that match the selected word
                    for (var i  = 0; i < this.uniqueWords.length; i++) {
                        if (this.uniqueWords[i].word == this.selection[0].word.toLowerCase()) {
                            this.uniqueWords[i].stage = stage;
                        }
                    }

                    // set stage for all words that match the selected word
                    for (var i  = 0; i < this.words.length; i++) {
                        if (this.words[i].word.toLowerCase() == this.selection[0].word.toLowerCase()) {
                            this.words[i].stage = stage;
                        }
                    }
                } else if (this.selectedPhrase !== -1) {
                    // set stage for all phrases that match the selected word
                    for (var i  = 0; i < this.phrases.length; i++) {
                        if (this.phrases[i].id == this.phrases[this.selectedPhrase].id) {
                            this.phrases[i].stage = stage;
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

                // unselect word if it was hovered
                if (hoverSetStage) {
                    this.unselectAllWords();
                }
            },
            updateSelectedWordStage() {
                if (this.selectedPhrase == -1 && this.selection.length) {
                    this.vocabBox.stage = parseInt(this.uniqueWords[this.selection[0].uniqueWordIndex].stage);
                } else if (this.selectedPhrase !== -1){
                    this.vocabBox.stage = parseInt(this.phrases[this.selectedPhrase].stage);
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
                for (var i = 0; i < this.words.length; i++) {
                    if (this.words[i].word == 'NEWLINE'
                        || sentenceIndexes.indexOf(this.words[i].sentence_index) == -1) {
                        continue;
                    }

                    exampleSentence.push({
                        word: this.words[i].word,
                        phrase_ids: []
                    });

                    if (withSpaces && this.words[i].spaceAfter) {
                        exampleSentence[exampleSentence.length - 1].word += ' ';
                    }
                }

                return exampleSentence;
            },
            updateExampleSentence() {
                var exampleSentence = this.getExampleSentence();

                var targetType = this.selection.length > 1 ? 'phrase' : 'word';
                var targetId = this.uniqueWords[this.selection[0].uniqueWordIndex].id;

                if (targetType == 'phrase') {
                    targetId = this.phrases[this.selectedPhrase].id;
                }

                axios.post('/vocabulary/example-sentence/create-or-update', {
                    targetType: targetType,
                    targetId: targetId,
                    exampleSentenceWords: JSON.stringify(exampleSentence),
                });
            },
            resizeHandle() {
                // update bottom sheet vocabulary
                this.vocabBox.vocabularyBottomSheetVisible = window.innerWidth <= 768;

                this.$nextTick(() => {
                    this.updateVocabBoxPosition();
                });
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
                    var selectedWordPositions = document.querySelector('[wordindex="' + this.selection[0].wordIndex + '"]').getBoundingClientRect();
                } else if (this.selection.length > 1) {
                    var selectedWordPositions = document.querySelector('[wordindex="' + this.selection[parseInt(this.selection.length / 2)].wordIndex + '"]').getBoundingClientRect();
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
                searchTerm = searchTerm.toLowerCase();
                var trimmedSearchTerm = searchTerm;

                // norwegian
                if (this.$props.language == 'norwegian' && searchTerm.substring(0, 2) == 'å ') {
                    trimmedSearchTerm = searchTerm.slice(2);
                }

                if (this.$props.language == 'norwegian' && searchTerm.substring(0, 3) == 'et ') {
                    trimmedSearchTerm = searchTerm.slice(3);
                }

                if (this.$props.language == 'norwegian' && searchTerm.substring(0, 3) == 'en ') {
                    trimmedSearchTerm = searchTerm.slice(3);
                }

                if (this.$props.language == 'norwegian' && searchTerm.substring(0, 3) == 'ei ') {
                    trimmedSearchTerm = searchTerm.slice(3);
                }

                // german
                if (this.$props.language == 'german' && searchTerm.substring(0, 4) == 'die ') {
                    trimmedSearchTerm = searchTerm.slice(4);
                }

                if (this.$props.language == 'german' && searchTerm.substring(0, 4) == 'der ') {
                    trimmedSearchTerm = searchTerm.slice(4);
                }

                if (this.$props.language == 'german' && searchTerm.substring(0, 4) == 'das ') {
                    trimmedSearchTerm = searchTerm.slice(4);
                }

                return trimmedSearchTerm;
            },
            getLeveledUpWordsAndPhrases() {
                let data = {
                    wordIds: [],
                    phraseIds: [],
                    wordsAndPhrases: [],
                }

                // collect words
                this.uniqueWords.forEach((word) => {
                    if (!word.definitions_checked && word.stage < 0) {
                        data.wordIds.push(word.id);
                        data.wordsAndPhrases.push(word);
                        data.wordsAndPhrases[data.wordsAndPhrases.length - 1].type = 'word';
                    }
                });

                // collect phrases
                this.phrases.forEach((phrase) => {
                    if (!phrase.definitions_checked && phrase.stage < 0) {
                        data.phraseIds.push(phrase.id);
                        data.wordsAndPhrases.push(phrase);
                        data.wordsAndPhrases[data.wordsAndPhrases.length - 1].type = 'phrase';
                    }
                });
                
                console.log(data);
                return data;
            }
        }
    }
</script>
