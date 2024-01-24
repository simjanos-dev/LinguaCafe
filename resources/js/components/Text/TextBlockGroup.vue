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
            ></text-block>
        </slot>

        <!--Vocabulary popup box-->
        <vocabulary-box
            v-if="(!$props.vocabularySidebar || !$props.vocabularySidebarFits) && modernVocabBox.active"
            ref="vocabularyBox"
            :language="$props.language"
            :active="modernVocabBox.active"
            :type="modernVocabBox.type"
            :positionLeft="modernVocabBox.positionLeft"
            :positionTop="modernVocabBox.positionTop"
            :width="modernVocabBox.width"
            :kanjiList="modernVocabBox.kanjiList"
            :word="modernVocabBox.word"
            :phrase="modernVocabBox.phrase"
            :stage="modernVocabBox.stage"
            :_reading="modernVocabBox.reading"
            :_baseWord="modernVocabBox.baseWord"
            :_baseWordReading="modernVocabBox.baseWordReading"
            :_phraseReading="modernVocabBox.phraseReading"
            :_translationText="modernVocabBox.translationText"
            :_searchField="modernVocabBox.searchField"
            @setStage="setStage"
            @unselectAllWords="unselectAllWords"
            @updateVocabBoxData="updateVocabBoxData"
            @addNewPhrase="addNewPhrase"
            @deletePhrase="deletePhrase"
            @addSelectedWordToAnki="addSelectedWordToAnki"
        ></vocabulary-box>

        <!--Vocabulary sidebar-->
        <vocabulary-side-box
            v-if="$props.vocabularySidebarFits && $props.vocabularySidebar"
            :key="'vocabulary-side-box-' + modernVocabBox.key"
            ref="vocabularySideBox"
            :language="$props.language"
            :active="modernVocabBox.active"
            :type="modernVocabBox.type"
            :positionLeft="modernVocabBox.positionLeft"
            :positionTop="modernVocabBox.positionTop"
            :height="modernVocabBox.height"
            :kanjiList="modernVocabBox.kanjiList"
            :word="modernVocabBox.word"
            :phrase="modernVocabBox.phrase"
            :stage="modernVocabBox.stage"
            :_reading="modernVocabBox.reading"
            :_baseWord="modernVocabBox.baseWord"
            :_baseWordReading="modernVocabBox.baseWordReading"
            :_phraseReading="modernVocabBox.phraseReading"
            :_translationText="modernVocabBox.translationText"
            :_searchField="modernVocabBox.searchField"
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
                modernVocabBox: {
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
            }
        },
        watch: {
            _textBlocks: function(newVal, oldVal) {
                this.textBlocks = newVal;
            }
        },
        mounted() {
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
        },
        methods: {
            updateSelection(newSelection, newSelectedPhrase, textBlockId) {
                this.modernVocabBox.tab = 0;
                this.selection = newSelection;
                this.selectedPhrase = newSelectedPhrase;
                this.selectedTextBlock = textBlockId;
                this.modernVocabBox.active = true;

                // update vocab box data
                this.modernVocabBox.key ++;

                this.modernVocabBox.searchField = '';
                this.modernVocabBox.translationText = '';
                this.modernVocabBox.word = '';
                this.modernVocabBox.phrase = [];
                this.modernVocabBox.reading = '';
                this.modernVocabBox.kanjiList = [];
                this.modernVocabBox.baseWord = '';
                this.modernVocabBox.baseWordReading = '';

                
                if (this.selection.length == 1) {
                    var uniqueWord = this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex];
                    this.modernVocabBox.type = 'word';
                    this.modernVocabBox.word = uniqueWord.word;
                    this.modernVocabBox.reading = uniqueWord.reading;
                    this.modernVocabBox.baseWord = uniqueWord.base_word;
                    this.modernVocabBox.baseWordReading = uniqueWord.base_word_reading;
                    this.modernVocabBox.translationText = uniqueWord.translation;
                    this.modernVocabBox.stage = uniqueWord.stage;
                    if (uniqueWord.base_word !== '') {
                        this.modernVocabBox.searchField = uniqueWord.base_word;

                        // remove unnecessary parts of the search term
                        this.modernVocabBox.searchField = this.trimSearchTerm(this.modernVocabBox.searchField);
                    } else {
                        this.modernVocabBox.searchField = uniqueWord.word;
                    }
                } else {
                    if (this.selectedPhrase !== -1) {
                        this.modernVocabBox.type = 'phrase';
                        this.modernVocabBox.reading = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].reading;
                        this.modernVocabBox.translationText = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].translation;
                        this.modernVocabBox.stage = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage;
                    } else {
                        this.modernVocabBox.type = 'new-phrase';
                    }

                    for (let i = 0; i < this.selection.length; i++) {
                        if (this.selection[i].word.toLowerCase() == 'newline') {
                            continue;
                        }
                     
                        if (this.selection.length > 1) {
                            this.modernVocabBox.phrase.push(this.selection[i]);
                        }

                        this.modernVocabBox.searchField += this.selection[i].word;
                        if (this.selection[i].spaceAfter) {
                            this.modernVocabBox.searchField += ' ';
                        }

                        if (this.selectedPhrase == -1) {
                            this.modernVocabBox.reading += this.selection[i].reading;
                        }
                    }
                }

                // collect unique kanji
                for (let wordIndex = 0; wordIndex < this.selection.length; wordIndex ++) {
                    var kanji = this.selection[wordIndex].kanji;
                    for (let kanjiIndex = 0; kanjiIndex < kanji.length; kanjiIndex ++) {
                        if (this.modernVocabBox.kanjiList.indexOf(kanji[kanjiIndex]) === -1) {
                            this.modernVocabBox.kanjiList.push(kanji[kanjiIndex]);
                        }
                    }
                }

                this.updateVocabBoxPosition();
                this.modernVocabBox.key ++;
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
                
                this.modernVocabBox.active = false;
            },
            unselectAllWords() {
                this.modernVocabBox.active = false;
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
                this.modernVocabBox.key ++;
                this.modernVocabBox.kanjiList = [];
                this.modernVocabBox.stage = 2;
                this.modernVocabBox.type = 'empty';
                this.modernVocabBox.word = '';
                this.modernVocabBox.phrase = [];
                this.modernVocabBox.searchField = '';
                this.modernVocabBox.translationText = '';
                this.modernVocabBox.reading = '';
                this.modernVocabBox.baseWord = '';
                this.modernVocabBox.baseWordReading = '';
                
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
                // get example sentence and add space. 
                var exampleSentence = this.getExampleSentence(true);
                var exampleSentenceText = '';
                for (let wordIndex = 0; wordIndex < exampleSentence.length; wordIndex++) {
                    exampleSentenceText += exampleSentence[wordIndex].word;
                }

                if (this.selection.length == 1) {
                    var data = {
                        word: this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].word,
                        reading: this.modernVocabBox.reading,
                        translation: this.modernVocabBox.translationText,
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
                        reading: this.modernVocabBox.reading,
                        translation: this.modernVocabBox.translationText,
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
                    reading: this.modernVocabBox.reading,
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
                this.modernVocabBox.type = 'phrase';
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
                            this.textBlocks[j].phrases[i].translation = this.modernVocabBox.translationText;
                            this.textBlocks[j].phrases[i].reading = this.modernVocabBox.reading;
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
                this.modernVocabBox.reading = newVocabBoxData.reading;
                this.modernVocabBox.baseWord = newVocabBoxData.baseWord;
                this.modernVocabBox.baseWordReading = newVocabBoxData.baseWordReading;
                this.modernVocabBox.phraseReading = newVocabBoxData.phraseReading;
                this.modernVocabBox.translationText = newVocabBoxData.translationText;
                
                this.$forceUpdate();
            },
            saveWord(withStage = false, exampleSentenceChanged = false) {
                var selectedWord = this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex];
                

                // update unique words in all blocks
                for (var j  = 0; j < this.textBlocks.length; j++) {
                    for (var i  = 0; i < this.textBlocks[j].uniqueWords.length; i++) {
                        if (this.textBlocks[j].uniqueWords[i].word.toLowerCase() == selectedWord.word.toLowerCase()) {
                            this.textBlocks[j].uniqueWords[i].translation = this.modernVocabBox.translationText;
                            this.textBlocks[j].uniqueWords[i].reading = this.modernVocabBox.reading;
                            this.textBlocks[j].uniqueWords[i].base_word = this.modernVocabBox.baseWord;
                            this.textBlocks[j].uniqueWords[i].base_word_reading = this.modernVocabBox.baseWordReading;
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
                    translation: this.modernVocabBox.translationText,
                    reading: this.modernVocabBox.reading,
                    base_word: this.modernVocabBox.baseWord,
                    base_word_reading: this.modernVocabBox.baseWordReading,
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
                if (this.ankiAutoAddCards && stage < 0 && (this.modernVocabBox.stage >= 0 || this.modernVocabBox.stage === undefined)) {
                    this.addSelectedWordToAnki();
                }
                
                // save word/phrase
                this.updateSelectedWordStage();
                if (save == 'word') {
                    this.saveWord(true, stage < 0);

                } else if (save == 'phrase') {
                    this.savePhrase(true, stage < 0);
                }

                this.modernVocabBox.stage = stage;
            },
            updateSelectedWordStage() {
                if (this.selectedPhrase == -1 && this.selection.length) {
                    this.modernVocabBox.stage = parseInt(this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].stage);
                } else if (this.selectedPhrase !== -1){
                    this.modernVocabBox.stage = parseInt(this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage);
                }

                if (this.modernVocabBox.stage == 2) {
                    this.modernVocabBox.stage = undefined;
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
                this.modernVocabBox.width = 400;
                var vocabBoxAreaElement = document.getElementsByClassName('vocab-box-area')[0];
                var vocabBoxArea = vocabBoxAreaElement.getBoundingClientRect();

                // update sidebar
                if (this.$props.vocabularySidebarFits && this.$props.vocabularySidebar) {
                    this.modernVocabBox.height = vocabBoxAreaElement.offsetHeight;
                    this.modernVocabBox.positionLeft = vocabBoxArea.right;
                    this.modernVocabBox.positionTop = vocabBoxArea.top;
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

                this.modernVocabBox.positionLeft = selectedWordPositions.right - vocabBoxArea.left - this.modernVocabBox.width / 2 - (selectedWordPositions.right - selectedWordPositions.left) / 2;

                if (window.innerWidth  < 440) {
                    this.modernVocabBox.positionLeft = 0;
                } else if (this.modernVocabBox.positionLeft < margin) {
                    this.modernVocabBox.positionLeft = margin;
                } else if (this.modernVocabBox.positionLeft > vocabBoxArea.right - vocabBoxArea.left - this.modernVocabBox.width - margin) {
                    this.modernVocabBox.positionLeft = vocabBoxArea.right - vocabBoxArea.left - this.modernVocabBox.width - margin;
                }

                this.modernVocabBox.positionTop = selectedWordPositions.bottom - vocabBoxArea.top + vocabBoxAreaElement.scrollTop + 25;

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
                if (this.$props.language == 'norwegian' && this.modernVocabBox.searchField.substring(0, 2) == 'å ') {
                    trimmedSearchTerm = this.modernVocabBox.searchField.slice(2);
                }

                if (this.$props.language == 'norwegian' && this.modernVocabBox.searchField.substring(0, 3) == 'et ') {
                    trimmedSearchTerm = this.modernVocabBox.searchField.slice(3);
                }

                if (this.$props.language == 'norwegian' && this.modernVocabBox.searchField.substring(0, 3) == 'en ') {
                    trimmedSearchTerm = this.modernVocabBox.searchField.slice(3);
                }

                if (this.$props.language == 'norwegian' && this.modernVocabBox.searchField.substring(0, 3) == 'ei ') {
                    trimmedSearchTerm = this.modernVocabBox.searchField.slice(3);
                }

                // german
                if (this.$props.language == 'german' && this.modernVocabBox.searchField.substring(0, 4) == 'die ') {
                    trimmedSearchTerm = this.modernVocabBox.searchField.slice(4);
                }

                if (this.$props.language == 'german' && this.modernVocabBox.searchField.substring(0, 4) == 'der ') {
                    trimmedSearchTerm = this.modernVocabBox.searchField.slice(4);
                }

                if (this.$props.language == 'german' && this.modernVocabBox.searchField.substring(0, 4) == 'das ') {
                    trimmedSearchTerm = this.modernVocabBox.searchField.slice(4);
                }

                return trimmedSearchTerm;
            }
        }
    }
</script>
