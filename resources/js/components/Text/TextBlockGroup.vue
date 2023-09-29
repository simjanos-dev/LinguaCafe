<template>
    <div class="text-block-group w-100" @mouseup="unselectAllWords">
        <slot
            :textBlocks="textBlocks"
            :language="language"
            :highlightWords="highlightWords"
            :plainTextMode="plainTextMode"
            :fontSize="fontSize"
            :lineSpacing="lineSpacing"
            :updateSelection="updateSelection"
            :unselectAllWords="unselectAllWords"
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
                :highlightWords="highlightWords"
                :plainTextMode="plainTextMode"
                :fontSize="fontSize"
                :lineSpacing="lineSpacing"
                @textSelected="updateSelection"
                @unselectAllWords="unselectAllWords"
            ></text-block>
        </slot>

        <!-- Vocab box -->
        <v-card 
            v-if="selection.length"
            id="vocab-box" 
            :class="{
                'new-phrase': selection.length > 1 && selectedPhrase == 1, 
                'rounded-lg': true,
                'closed': vocabBox.closed
            }" 
            :style="{
                'top': vocabBox.position.top + 'px', 
                'left': vocabBox.position.left + 'px',
                'width': vocabBox.width + 'px'
            }"
            @mouseup.stop=";"
        >
            <v-tabs id="vocab-box-tabs" grow background-color="primary" height="36" v-model="vocabBox.tab" @change="scrollToVocabBox">
                <v-tab class="px-2" v-if="selection.length == 1">Word</v-tab>
                <v-tab class="px-2" v-if="selection.length > 1">Phrase</v-tab>
                <v-tab class="px-2">Edit</v-tab>
                <v-tab class="px-2">Inflections</v-tab>
            </v-tabs>
            <v-overlay class="text-center rounded-lg" absolute :value="phraseCurrentlySaving" opacity="0.6" v-if="selection.length > 1 && phraseCurrentlySaving">
                <span class="h5">Saving phrase, please wait a second.</span><br><br>
                <v-progress-circular indeterminate size="64" color="white"></v-progress-circular>
            </v-overlay>
            <v-card-text class="pa-2">
                <v-tabs-items v-model="vocabBox.tab">
                    <!-- Word/phrase tab -->
                    <v-tab-item :value="0">
                        <!-- Single word -->
                        <template v-if="selection.length == 1">
                            <div class="vocab-box-subheader">Word</div>
                            <!-- With base word -->
                            <div id="word" class="pl-2" v-if="textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].base_word !== ''">
                                <ruby>{{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].base_word}}<rt>{{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].base_word_reading}}</rt></ruby>
                                <v-icon>mdi-arrow-right-thick</v-icon>
                                <ruby>{{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].word}}<rt>{{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].reading}}</rt></ruby>
                            </div>
                            
                            <!-- No base word -->
                            <div id="word" class="pl-2" v-if="textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].base_word == ''">
                                <ruby>{{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].word}}<rt>{{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].reading}}</rt></ruby>
                            </div>
                        </template>
                        
                        <!-- Phrase -->
                        <template v-if="selection.length > 1">
                            <div class="vocab-box-subheader">Phrase</div>
                            <!-- Phrase text -->
                            <div id="phrase" class="pl-2">
                                <template v-for="(word, index) in selection" v-if="word.word !== 'NEWLINE'">{{ word.word }}</template>
                            </div>

                            <!-- Phrase reading -->
                            <template>
                                <div class="vocab-box-subheader mt-2">Reading</div>
                                <div id="reading" class="pl-2">{{ vocabBox.reading }}</div>
                            </template>
                        </template>

                        <!-- Translation -->
                        <template v-if="vocabBox.translationText.length">
                            <div class="vocab-box-subheader mt-2">Definitions</div>
                            <ul id="definitions" class="ma-0">
                                <template>
                                    <li v-for="translation, index in vocabBox.translationList" :key="index">{{ translation }}</li>
                                </template>
                            </ul>
                        </template>

                        <!-- Stage buttons-->
                        <template v-if="selection.length == 1 || selectedPhrase !== -1">
                            <div class="vocab-box-subheader mt-2">Stage</div>
                            <div :class="{'d-block': true, 'text-center': true, 'mt-1': false, 'mb-6': selection.length == 1}">
                                <div id="stage-buttons" class="v-item-group theme--light v-btn-toggle v-btn-toggle--rounded primary--text">
                                    <v-btn :value="-7" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == -7}" @click="setStage(-7)">7</v-btn>
                                    <v-btn :value="-6" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == -6}" @click="setStage(-6)">6</v-btn>
                                    <v-btn :value="-5" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == -5}" @click="setStage(-5)">5</v-btn>
                                    <v-btn :value="-4" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == -4}" @click="setStage(-4)">4</v-btn>
                                    <v-btn :value="-3" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == -3}" @click="setStage(-3)">3</v-btn>
                                    <v-btn :value="-2" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == -2}" @click="setStage(-2)">2</v-btn>
                                    <v-btn :value="-1" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == -1}" @click="setStage(-1)">1</v-btn>
                                    <v-btn :value="0" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == 0}" @click="setStage(0)"><v-icon>mdi-check</v-icon></v-btn>
                                    <v-btn :value="1" :class="{'stage-button': true, 'v-btn--active': vocabBox.selectedStageButton == 1}" @click="setStage(1)" v-if="selection.length == 1"><v-icon>mdi-close</v-icon></v-btn>
                                </div>
                            </div>
                        </template>

                        <!-- Save and delete phrase buttons -->
                        <v-card-actions class="pa-0">
                            <v-spacer></v-spacer>
                            <v-btn 
                                class="mt-2"
                                small
                                rounded
                                color="success"
                                @click="addNewPhrase"
                                v-if="selection.length > 1 && selectedPhrase == -1"
                            >Save phrase</v-btn>
                            <v-btn 
                                class="mt-2"
                                small
                                rounded
                                color="error"
                                @click="deletePhrase"
                                v-if="selectedPhrase !== -1"
                            >Delete phrase</v-btn>
                        </v-card-actions>
                    </v-tab-item>

                    <!-- Edit tab -->
                    <v-tab-item :value="1">
                        <!-- Word editing -->
                        <v-simple-table dense id="word-edit-table" class="no-hover mx-auto" v-if="selection.length == 1">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Base</th>
                                    <th></th>
                                    <th>Selected</th>
                                </tr>
                            </thead>
                                <tbody>
                                <tr>
                                    <th>Word</th>
                                    <td>
                                        <v-text-field 
                                            class="word-input"
                                            filled
                                            dense
                                            hide-details
                                            v-model="vocabBox.base_word"
                                        ></v-text-field>
                                    </td>
                                    <td><v-icon>mdi-arrow-right-thick</v-icon></td>
                                    <td id="selected-word-field" class="text-left pl-4">{{ textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].word }}</td>
                                </tr>
                                <tr>
                                    <th>Reading</th>
                                    <td>
                                        <v-text-field 
                                            class="word-input my-1"
                                            filled
                                            dense
                                            hide-details
                                            v-model="vocabBox.base_word_reading"
                                        ></v-text-field>
                                    </td>
                                    <td><v-icon>mdi-arrow-right-thick</v-icon></td>
                                    <td>
                                        <v-text-field 
                                            class="word-input my-1"
                                            filled
                                            dense
                                            hide-details
                                            v-model="vocabBox.reading"
                                        ></v-text-field>
                                    </td>
                                </tr>
                            </tbody>
                        </v-simple-table>
                        
                        <!-- Phrase editing -->
                        <template v-if="selection.length > 1">
                            <div class="vocab-box-subheader">Phrase reading</div>
                            <v-textarea
                                filled
                                dense
                                no-resize
                                hide-details
                                height="80px"
                                v-model="vocabBox.reading"
                            ></v-textarea>
                        </template>

                        <!-- Translation editing -->
                        <div class="vocab-box-subheader mt-2">Translation</div>
                        <v-textarea
                            filled
                            dense
                            no-resize
                            hide-details
                            height="80px"
                            v-model="vocabBox.translationText"
                            @change="updateVocabBoxTranslationList"
                        ></v-textarea>

                        <!-- Search term -->
                        <div class="vocab-box-subheader mt-2">Dictionary search</div>
                        <v-text-field 
                            class="mb-3"
                            filled
                            dense
                            hide-details
                            width="100%"
                            v-model="vocabBox.searchField"
                            @change="makeSearchRequest"
                        ></v-text-field>

                        <!-- Search results -->
                        <div id="search-results" class="mb-4 pa-2">
                            <div class="search-result jmdict" v-for="(searchResult, searchresultIndex) in vocabBox.searchResults" :key="searchresultIndex">
                                <div class="search-result-title rounded px-2">{{ searchResult.word }} <div class="dictionary">jmdict</div></div>
                                <div class="search-result-definition rounded" v-for="(definition, definitionIndex) in searchResult.definitions" :key="definitionIndex" @click="addDefinitionToInput(definition)">
                                    {{ definition }} <v-icon>mdi-plus</v-icon>
                                </div>
                                <template v-if="searchResult.otherForms.length">
                                    <div class="vocab-box-subheader">Other forms:</div>
                                    <div class="d-flex flex-wrap">
                                        <div v-for="(form, formIndex) in searchResult.otherForms" :key="formIndex">
                                            {{ form }}<span class="mr-2" v-if="formIndex < searchResult.otherForms.length - 1">, </span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                    </v-tab-item>

                    <!-- Inflections tab -->
                    <!-- <v-tab-item :value="2" class="pb-4">
                        <v-simple-table dense id="inflections-table" class="no-hover mx-auto" v-if="inflections.length">
                            <thead>
                                <tr>
                                    <th>Form</th>
                                    <th>Affirmative</th>
                                    <th>Negative</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(inflection, index) in inflections" :key="index">
                                    <td class="px-2">{{ inflection.name }}</td>
                                    <td class="px-1">{{ inflection.affPlain }}</td>
                                    <td class="px-1">{{ inflection.negPlain }}</td>
                                </tr>
                            </tbody>
                        </v-simple-table>
                    </v-tab-item> -->
                </v-tabs-items>
            </v-card-text>
            <v-btn id="close-vocab-box-button" rounded elevation="2" color="error" @click="unselectAllWords"><v-icon>mdi-close</v-icon> Close</v-btn>
        </v-card>
    </div>
</template>

<script>
    export default {
        data: function() {
            return {
                textBlocks: this.$props._textBlocks,
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
            highlightWords: Boolean,
            plainTextMode: Boolean,
            fontSize: Number,
            lineSpacing: Number
        },
        watch: {
            _textBlocks: function(newVal, oldVal) {
                this.textBlocks = newVal;
            }
        },
        mounted() {
            window.addEventListener('resize', this.updateVocabBoxPosition);
        },  
        methods: {
            updateSelection: function(newSelection, newSelectedPhrase, textBlockId) {
                this.vocabBox.tab = 0;
                this.selection = newSelection;
                this.selectedPhrase = newSelectedPhrase;
                this.selectedTextBlock = textBlockId;

                // update vocab box data
                this.vocabBox.closed = false;
                this.vocabBox.searchField = '';
                this.vocabBox.translationText = '';
                this.vocabBox.reading = '';
                this.vocabBox.base_word = '';
                this.vocabBox.base_word_reading = '';

                let uniqueWord = this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex];
                
                if (this.selection.length == 1) {
                    this.vocabBox.translationText = uniqueWord.translation;
                    this.vocabBox.reading = uniqueWord.reading;
                    this.vocabBox.base_word = uniqueWord.base_word;
                    this.vocabBox.base_word_reading = uniqueWord.base_word_reading;
                    if (uniqueWord.base_word !== '') {
                        this.vocabBox.searchField = uniqueWord.base_word;
                    } else {
                        this.vocabBox.searchField = uniqueWord.word;
                    }
                } else {
                    if (this.selectedPhrase !== -1) {
                        this.vocabBox.reading = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].reading;
                        this.vocabBox.translationText = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].translation;
                    }

                    for (let i = 0; i < this.selection.length; i++) {
                        if (this.selection[i].word.toLowerCase() == 'newline') {
                            continue;
                        }

                        this.vocabBox.searchField += this.selection[i].word;
                        if (this.selectedPhrase == -1) {
                            this.vocabBox.reading += this.selection[i].reading;
                        }
                    }
                }

                this.makeSearchRequest();
                this.updateVocabBoxTranslationList();
                this.updateVocabBoxPosition();
                this.updateSelectedWordStage();
            },
            unselectAllWords: function(fast = false) {
                if (this.selection.length == 1) {
                    this.saveWord();
                } else if (this.selectedPhrase !== -1) {
                    this.savePhrase();
                }

                this.vocabBox.closed = true;
                this.selectedPhrase = -1;
                this.selection = [];
                let delay = fast ? 0 : 120;

                if (delay) {
                    setTimeout(this.unselectAllWordsProcess, delay);
                } else {
                    this.unselectAllWordsProcess();
                }
            },
            unselectAllWordsProcess: function() {
                this.selectedPhrase = -1;
                this.selection = [];
                this.vocabBox.searchField = '';
                this.vocabBox.translationText = '';
                this.vocabBox.reading = '';
                this.vocabBox.base_word = '';
                this.vocabBox.base_word_reading = '';
                
                for (let j = 0; j < this.textBlocks.length; j++) {
                    this.unselectWordInTextBlock(j);
                    for (let i  = 0; i < this.textBlocks[j].words.length; i++) {
                        this.textBlocks[j].words[i].selected = false;
                        this.textBlocks[j].words[i].hover = false;
                    }
                }
            },
            unselectWordInTextBlock: function(textBlockId) {
                for (var i = 0; i < this.$children.length; i++) {
                    if (this.$children[i].textBlockId === undefined) {
                        continue;
                    }

                    if (this.$children[i].textBlockId === textBlockId) {
                        this.$children[i].unselectWord();
                    }
                }
            },
            updateVocabBoxTranslationList: function() {
                this.vocabBox.translationList = this.vocabBox.translationText.split(';');
            },
            updateSelectedWordLookupCount: function(id) {

            },
            addNewPhrase: function() {
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
            },
            getSelectedPhraseIndex: function() {
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
            deletePhrase: function() {
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
            savePhrase: function(withStage = false, exampleSentenceChanged = false) {
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
                };

                if (this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].id == -1) {
                    saveData.stage = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage;
                } else {
                    saveData.id = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].id;
                }

                if (withStage) {
                    saveData.stage = this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage;
                }

                axios.post('/vocabulary/phrase/update', saveData).then((response) => {
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
                    if (exampleSentenceChanged) {
                        this.updateExampleSentence();
                    }
                });
            },
            updatePhraseBorders: function() {
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
            saveWord: function(withStage = false, exampleSentenceChanged = false) {
                var selectedWord = this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex];
                

                for (var j  = 0; j < this.textBlocks.length; j++) {
                    for (var i  = 0; i < this.textBlocks[j].uniqueWords.length; i++) {
                        if (this.textBlocks[j].uniqueWords[i].word.toLowerCase() == selectedWord.word.toLowerCase()) {
                            this.textBlocks[j].uniqueWords[i].translation = this.vocabBox.translationText;
                            this.textBlocks[j].uniqueWords[i].reading = this.vocabBox.reading;
                            this.textBlocks[j].uniqueWords[i].base_word = this.vocabBox.base_word;
                            this.textBlocks[j].uniqueWords[i].base_word_reading = this.vocabBox.base_word_reading;
                        }
                    }
                }
                
                var saveData = {
                    id: selectedWord.id,
                    translation: selectedWord.translation,
                    reading: selectedWord.reading,
                    base_word: selectedWord.base_word,
                    base_word_reading: selectedWord.base_word_reading,
                    example_sentence: selectedWord.example_sentence,
                    lookup_count: selectedWord.lookup_count,
                };

                if (withStage) {
                    saveData.stage = selectedWord.stage;
                }

                axios.post('/vocabulary/update', saveData).then((response) => {
                }).catch(function (error) {
                    console.log(error);
                }).then(() => {
                    if (exampleSentenceChanged) {
                        this.updateExampleSentence();
                    }
                });
            },
            setStage: function(stage) {
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


                this.updateSelectedWordStage();
                

                if (save == 'word') {
                    this.saveWord(true, stage < 0);
                } else if (save == 'phrase') {
                    this.savePhrase(true, stage < 0);
                }
                
            },
            updateSelectedWordStage: function() {
                if (this.selectedPhrase == -1 && this.selection.length) {
                    this.vocabBox.selectedStageButton = parseInt(this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].stage);
                } else if (this.selectedPhrase !== -1){
                    this.vocabBox.selectedStageButton = parseInt(this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage);
                }

                if (this.vocabBox.selectedStageButton == 2) {
                    this.vocabBox.selectedStageButton = undefined;
                }
            },
            updateExampleSentence: function() {
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
                        word: this.textBlocks[this.selectedTextBlock].words[i].word.toLowerCase(),
                        phrase_ids: []
                    });
                }

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
            makeSearchRequest: function() {
                this.vocabBox.searchResults = [];
                this.inflections = [];
                if (!this.selection.length) {
                    return;
                }

                // search inflections
                axios.post('/dictionary/search/inflections', {
                    dictionary: 'jmdict',
                    term: this.vocabBox.searchField
                })
                .then(function (response) {
                    var data = response.data;
                    var displayedInflections = ['Non-past', 'Non-past, polite', 'Past', 'Past, polite', 'Te-form', 'Potential', 'Passive', 'Causative', 'Causative Passive', 'Imperative'];
                    
                    for (var i = 0; i < data.length; i++) {
                        if (!displayedInflections.includes(data[i].name)) {
                            continue;
                        }

                        var index = this.inflections.findIndex(item => item.name === data[i].name);
                        if (index == -1) {
                            this.inflections.push({
                                name: data[i].name,
                            });

                            index = this.inflections.length - 1;
                        }

                        // add different forms to the item
                        if (data[i].form == 'aff-plain:') {
                            this.inflections[index].affPlain = data[i].value;
                        }

                        if (data[i].form == 'aff-formal:') {
                            this.inflections[index].affFormal = data[i].value;
                        }

                        if (data[i].form == 'neg-plain:') {
                            this.inflections[index].negPlain = data[i].value;
                        }

                        if (data[i].form == 'neg-formal:') {
                            this.inflections[index].negFormal = data[i].value;
                        }
                    }
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                })
                .then(function () {
                });

                // search word
                axios.post('/dictionary/search', {
                    dictionary: 'jmdict',
                    term: this.vocabBox.searchField
                })
                .then(function (response) {
                    this.processSearchRequest(response.data);
                }.bind(this))
                .catch(function (error) {
                    console.log(error);
                })
                .then(function () {
                });
            },
            processSearchRequest: function(data) {
                this.vocabBox.searchResults = [];
                for (var i = 0; i < data.length; i++) {
                    this.vocabBox.searchResults.push({
                        word: data[i].words.shift(),
                        otherForms: data[i].words,
                        definitions: data[i].definitions
                    });
                }

                this.$nextTick(() => {
                    var element = document.getElementById('translations');
                    if (element == null) {
                        return;
                    }

                    this.allSearchResultsVisible = element.scrollHeight <= element.clientHeight;
                    this.showAllSearchResults = false;
                });
            },
            addDefinitionToInput: function(definition) {
                if (this.vocabBox.translationText.length && this.vocabBox.translationText[this.vocabBox.translationText.length - 1] !== ';') {
                    this.vocabBox.translationText += ';';
                }

                this.vocabBox.translationText += definition;
                this.updateVocabBoxTranslationList();
            },
            updateVocabBoxPosition: function() {
                var margin = 8;
                this.vocabBox.width = window.innerWidth > 440 ? 400 : window.innerWidth - 24;

                if (!this.selection.length) {
                    return;
                }

                var vocabBoxAreaElement = document.getElementsByClassName('vocab-box-area')[0];
                var vocabBoxArea = vocabBoxAreaElement.getBoundingClientRect();
                if (this.selection.length == 1) {
                    var selectedWordPositions = document.querySelector('[textblock="' + this.selectedTextBlock + '"] [wordindex="' + this.selection[0].wordIndex + '"]').getBoundingClientRect();
                } else if (this.selection.length > 1) {
                    var selectedWordPositions = document.querySelector('[textblock="' + this.selectedTextBlock + '"] [wordindex="' + this.selection[parseInt(this.selection.length / 2)].wordIndex + '"]').getBoundingClientRect();
                }

                this.vocabBox.position.left = selectedWordPositions.right - vocabBoxArea.left - this.vocabBox.width / 2 - (selectedWordPositions.right - selectedWordPositions.left) / 2;

                if (window.innerWidth  < 440) {
                    this.vocabBox.position.left = 0;
                } else if (this.vocabBox.position.left < margin) {
                    this.vocabBox.position.left = margin;
                } else if (this.vocabBox.position.left > vocabBoxArea.right - vocabBoxArea.left - this.vocabBox.width - margin) {
                    this.vocabBox.position.left = vocabBoxArea.right - vocabBoxArea.left - this.vocabBox.width - margin;
                }

                var appElement = document.getElementById('app');
                var bodyElement = document.body;
                var scrollTop = appElement.scrollTop ? appElement.scrollTop : bodyElement.scrollTop;

                console.log('vocab box top', vocabBoxArea.top, selectedWordPositions.bottom);
                this.vocabBox.position.top = selectedWordPositions.bottom - vocabBoxArea.top + 15;

                this.scrollToVocabBox();
            },
            scrollToVocabBox: function() {
                setTimeout(() => {
                    var vocabBox = document.getElementById('vocab-box');
                    if (vocabBox) {
                        vocabBox.scrollIntoViewIfNeeded(false);
                    }
                }, 450);
            },
        }
    }
</script>
