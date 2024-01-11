<template>
    <div class="text-block-group w-100" @mouseup="unselectAllWords">
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
            :updateSelection="updateSelection"
            :saveSelectedWord="saveSelectedWord"
            :updateLookupCount="updateLookupCount"
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
                @textSelected="updateSelection"
                @saveSelectedWord="saveSelectedWord"
                @updateLookupCount="updateLookupCount"
            ></text-block>
        </slot>

        <!-- Vocab box -->
        <v-card 
            v-if="selection.length"
            id="vocab-box" 
            :class="{
                'new-phrase': selection.length > 1 && selectedPhrase == 1, 
                'rounded-lg': true,
                'closed': vocabBox.closed,
                'd-flex': true
            }" 
            :style="{
                'top': vocabBox.position.top + 'px', 
                'left': vocabBox.position.left + 'px',
                'width': vocabBox.width + 'px'
            }"
            @mouseup.stop=";"
        >
            <v-overlay 
                v-if="selection.length > 1 && phraseCurrentlySaving"
                class="text-center rounded-lg" 
                absolute 
                :value="phraseCurrentlySaving" 
                opacity="0.6" 
            >
                <span class="h5">Saving phrase, please wait a second.</span><br><br>
                <v-progress-circular indeterminate size="64" color="white"></v-progress-circular>
            </v-overlay>
            
            <!-- Vocab box content -->
            <div class="vocab-box-content pa-4 pb-1">
                <v-tabs-items v-model="vocabBox.tab">
                    <!-- Word info page -->
                    <v-tab-item :value="0">
                        <v-card-text class="pa-0">
                            <!-- Single word -->
                            <template v-if="selection.length == 1">
                                <div class="vocab-box-subheader mb-2 mt-0"><span class="rounded-pill py-1 px-3">Word</span></div>
                                <!-- With base word -->
                                <div class="expression" v-if="textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].base_word !== ''">
                                    <ruby>
                                        {{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].base_word}}
                                        <rt v-if="$props.language == 'japanese'">
                                            {{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].base_word_reading}}
                                        </rt>
                                    </ruby>
                                    <v-icon color="text">mdi-arrow-right-thick</v-icon>
                                    <ruby>
                                        {{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].word}}
                                        <rt v-if="$props.language == 'japanese'">
                                            {{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].reading}}
                                        </rt>
                                    </ruby>
                                </div>
                                
                                <!-- No base word -->
                                <div 
                                    class="expression" 
                                    v-if="textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].base_word == ''"
                                >
                                    <ruby>
                                        {{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].word}}
                                        <rt v-if="$props.language == 'japanese'">
                                            {{textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].reading}}
                                        </rt>
                                    </ruby>
                                </div>
                            </template>

                            <!-- Phrase -->
                            <template v-if="selection.length > 1">
                                <div class="vocab-box-subheader mb-2 mt-0"><span class="rounded-pill py-1 px-3">Phrase</span></div>
                                <!-- Phrase text -->
                                <div class="expression">
                                    <template v-for="(word, index) in selection" v-if="word.word !== 'NEWLINE'">
                                        <span :class="{'mr-2': word.spaceAfter}">{{ word.word }}</span>
                                    </template>
                                </div>

                                <!-- Phrase reading -->
                                <template v-if="$props.language == 'japanese'">
                                    <div class="vocab-box-subheader mb-2 mt-4"><span class="rounded-pill py-1 px-3">Reading</span></div>
                                    <div class="expression">{{ vocabBox.reading }}</div>
                                </template>
                            </template>
                            
                            <!-- Kanji list -->
                            <template v-if="vocabBox.kanji.length">
                                <div class="vocab-box-subheader mb-2 mt-4"><span class="rounded-pill py-1 px-3">Kanji</span></div>
                                <div id="vocab-box-kanji-box" class="d-flex flex-wrap ma-0">
                                    <div 
                                        class="kanji rounded-lg mr-2" 
                                        v-for="kanji, index in vocabBox.kanji" 
                                        :key="index"
                                        @click="openKanji(kanji)"
                                    >
                                        {{ kanji }}
                                    </div>
                                </div>
                            </template>

                            <!-- Definitions -->
                            <template v-if="vocabBox.translationText.length">
                                <div class="vocab-box-subheader mb-2 mt-4"><span class="rounded-pill py-1 px-3">Definitions</span></div>
                                <ul id="definitions" class="ma-0">
                                    <li v-for="translation, index in vocabBox.translationList" :key="index">{{ translation }}</li>
                                </ul>
                            </template>

                            <!-- Stage buttons-->
                            <template v-if="selection.length == 1 || selectedPhrase !== -1">
                                <div class="vocab-box-subheader mb-2 mt-4"><span class="rounded-pill py-1 px-3">Level</span></div>
                                <div id="vocab-box-stage-buttons" class="mb-2">
                                    <v-btn :class="{'v-btn--active': vocabBox.selectedStageButton == -7}" @click="setStage(-7)">7</v-btn>
                                    <v-btn :class="{'v-btn--active': vocabBox.selectedStageButton == -6}" @click="setStage(-6)">6</v-btn>
                                    <v-btn :class="{'v-btn--active': vocabBox.selectedStageButton == -5}" @click="setStage(-5)">5</v-btn>
                                    <v-btn :class="{'v-btn--active': vocabBox.selectedStageButton == -4}" @click="setStage(-4)">4</v-btn>
                                    <v-btn :class="{'v-btn--active': vocabBox.selectedStageButton == -3}" @click="setStage(-3)">3</v-btn>
                                    <v-btn :class="{'v-btn--active': vocabBox.selectedStageButton == -2}" @click="setStage(-2)">2</v-btn>
                                    <v-btn :class="{'v-btn--active': vocabBox.selectedStageButton == -1}" @click="setStage(-1)">1</v-btn>
                                    <v-btn 
                                        :class="{'v-btn--active': vocabBox.selectedStageButton == 0}"
                                        @click="setStage(0)" 
                                    >
                                        <v-icon>mdi-check</v-icon>
                                    </v-btn>
                                    <v-btn 
                                        :class="{'v-btn--active': vocabBox.selectedStageButton == 1}" 
                                        @click="setStage(1)" 
                                        v-if="selection.length == 1"
                                    >
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn>
                                </div>
                            </template>
                        </v-card-text>

                        <v-card-actions v-if="selection.length > 1" class="mt-2 pl-0">
                            <v-btn 
                                small
                                rounded
                                color="success"
                                @click="addNewPhrase"
                                v-if="selection.length > 1 && selectedPhrase == -1"
                            >Save phrase</v-btn>
                            <v-btn 
                                small
                                rounded
                                color="error"
                                @click="deletePhrase"
                                v-if="selectedPhrase !== -1"
                            >Delete phrase</v-btn>
                        </v-card-actions>
                    </v-tab-item>

                    <!-- Editing page -->
                    <v-tab-item :value="1">
                        <v-card-text id="vocab-box-edit-page" class="pa-0">
                            <!-- Word text fields -->
                            <div class="d-flex" v-if="selection.length == 1">
                                <v-text-field 
                                    :class="{'mt-2': true, 'mb-2': $props.language !== 'japanese'}"
                                    hide-details
                                    label="Lemma"
                                    filled
                                    dense
                                    rounded
                                    v-model="vocabBox.base_word"
                                ></v-text-field>
                                <v-text-field 
                                    :class="{'mt-2': true, 'mb-2': $props.language !== 'japanese'}"
                                    hide-details
                                    label="Word"
                                    disabled
                                    filled
                                    dense
                                    rounded
                                    :value="textBlocks[selectedTextBlock].uniqueWords[selection[0].uniqueWordIndex].word"
                                ></v-text-field>
                            </div>

                            <!-- Reading fields -->
                            <div class="d-flex" v-if="selection.length == 1 && $props.language == 'japanese'">
                                <v-text-field 
                                    class="my-2"
                                    hide-details
                                    label="Lemma reading"
                                    filled
                                    dense
                                    rounded
                                    v-model="vocabBox.base_word_reading"
                                ></v-text-field>
                                <v-text-field 
                                    class="my-2"
                                    hide-details
                                    label="Reading"
                                    filled
                                    dense
                                    rounded
                                    v-model="vocabBox.reading"
                                ></v-text-field>
                            </div>

                            <!-- Phrase fields -->
                            <v-textarea
                                v-if="selection.length > 1 && $props.language == 'japanese'"
                                class="my-2"
                                label="Reading"
                                filled
                                dense
                                no-resize
                                rounded
                                hide-details
                                height="100"
                                v-model="vocabBox.reading"
                            ></v-textarea>

                            <!-- Translation -->
                            <v-textarea
                                :class="{'mt-2': $props.language !== 'japanese'}"
                                label="Translation"
                                filled
                                dense
                                no-resize
                                rounded
                                hide-details
                                height="100"
                                v-model="vocabBox.translationText"
                                @change="updateVocabBoxTranslationList"
                            ></v-textarea>

                            <!-- Search term -->
                            <!-- <div class="vocab-box-subheader mt-2">Dictionary search</div> -->
                            <v-text-field 
                                label="Dictionary search"
                                class="mt-2 mb-3"
                                filled
                                dense
                                rounded
                                width="100%"
                                hide-details
                                v-model="vocabBox.searchField"
                                @change="makeSearchRequest"
                            ></v-text-field>

                            <!-- Search results -->
                            <div id="search-results" class="mb-4 pa-2">
                                <div class="search-result jmdict" v-for="(searchResult, searchresultIndex) in vocabBox.searchResults" :key="searchresultIndex">
                                    <!-- Regular record -->
                                    <template v-if="searchResult.dictionary !== 'JMDict'">
                                        <div v-for="(record, recordIndex) in searchResult.records" :key="recordIndex">
                                            <div class="search-result-title rounded px-2" :style="{'background-color': searchResult.color}">{{ record.word }} <div class="dictionary"> {{ searchResult.dictionary}} </div></div>
                                            <div 
                                                v-for="(definition, definitionIndex) in record.definitions" 
                                                :key="definitionIndex" 
                                                class="search-result-definition rounded"
                                                @click="addDefinitionToInput(definition)"
                                            >
                                                {{ definition }} <v-icon>mdi-plus</v-icon>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- JMDict record -->
                                    <template v-if="searchResult.dictionary == 'JMDict'">
                                        <div v-for="(record, recordIndex) in searchResult.records" :key="recordIndex">
                                            <div class="search-result-title rounded px-2" :style="{'background-color': searchResult.color}">{{ record.word }} <div class="dictionary"> {{ searchResult.dictionary}} </div></div>
                                            <div class="search-result-definition rounded" v-for="(definition, definitionIndex) in record.definitions" :key="definitionIndex" @click="addDefinitionToInput(definition)">
                                                {{ definition }} <v-icon>mdi-plus</v-icon>
                                            </div>
                                        
                                            <template v-if="record.otherForms.length">
                                                <div class="vocab-box-subheader">Other forms:</div>
                                                <div class="d-flex flex-wrap">
                                                    <div v-for="(form, formIndex) in record.otherForms" :key="formIndex">
                                                        {{ form }}<span class="mr-2" v-if="formIndex < record.otherForms.length - 1">, </span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </v-card-text>
                    </v-tab-item>
                </v-tabs-items>
            </div>

            <!-- Vocab box toolbar -->
            <div class="vocab-box-toolbar d-flex flex-column align-center flex-wrap pt-1 rounded-r-lg">
                <v-btn dark icon @click="unselectAllWords" title="Close"><v-icon>mdi-close</v-icon></v-btn>
                <v-btn dark icon @click="openVocabBoxEditPage" title="Edit" v-if="vocabBox.tab == 0"><v-icon>mdi-pencil</v-icon></v-btn>
                <v-btn dark icon @click="vocabBox.tab = 0;" v-if="vocabBox.tab == 1" title="Back"><v-icon>mdi-arrow-left</v-icon></v-btn>
                <v-menu left offset-y class="rounded-lg">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn dark icon v-bind="attrs" v-on="on" title="More options">
                            <v-icon>mdi-dots-horizontal</v-icon>
                        </v-btn>
                    </template>
                    <v-btn 
                        v-if="selection.length === 1 || selectedPhrase !== -1"
                        class="menu-button justify-start" 
                        @click="addSelectedWordToAnki"
                    >
                        <v-icon class="mr-1">mdi-cards</v-icon>Send to anki
                    </v-btn>
                </v-menu>
            </div>
        </v-card>
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
                    kanji: [],
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
            }

        },
        watch: {
            _textBlocks: function(newVal, oldVal) {
                this.textBlocks = newVal;
            }
        },
        mounted() {
            window.addEventListener('resize', this.updateVocabBoxPosition);

            axios.post('/settings/get-by-name', {
                'settingNames': [
                    'ankiAutoAddCards',
                    'ankiShowNotifications'
                ]
            }).then((response) => {
                this.ankiAutoAddCards = response.data.ankiAutoAddCards;
                this.ankiShowNotifications = response.data.ankiShowNotifications;
            });
        },  
        methods: {
            updateSelection(newSelection, newSelectedPhrase, textBlockId) {
                this.vocabBox.tab = 0;
                this.selection = newSelection;
                this.selectedPhrase = newSelectedPhrase;
                this.selectedTextBlock = textBlockId;

                // update vocab box data
                this.vocabBox.closed = false;
                this.vocabBox.searchField = '';
                this.vocabBox.translationText = '';
                this.vocabBox.reading = '';
                this.vocabBox.kanji = [];
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

                        // temporary search fix for norwegian
                        if (this.$props.language == 'norwegian' && this.vocabBox.searchField.substring(0, 2) == 'å ') {
                            this.vocabBox.searchField = this.vocabBox.searchField.slice(2);
                        }

                        if (this.$props.language == 'norwegian' && this.vocabBox.searchField.substring(0, 3) == 'et ') {
                            this.vocabBox.searchField = this.vocabBox.searchField.slice(3);
                        }

                        if (this.$props.language == 'norwegian' && this.vocabBox.searchField.substring(0, 3) == 'en ') {
                            this.vocabBox.searchField = this.vocabBox.searchField.slice(3);
                        }

                        if (this.$props.language == 'norwegian' && this.vocabBox.searchField.substring(0, 3) == 'ei ') {
                            this.vocabBox.searchField = this.vocabBox.searchField.slice(3);
                        }
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
                        if (this.vocabBox.kanji.indexOf(kanji[kanjiIndex]) === -1) {
                            this.vocabBox.kanji.push(kanji[kanjiIndex]);
                        }
                    }
                }

                this.updateVocabBoxTranslationList();
                this.updateVocabBoxPosition();
                this.updateSelectedWordStage();
            },
            saveSelectedWord() {
                if (this.selection.length == 1) {
                    this.saveWord();
                } else if (this.selectedPhrase !== -1) {
                    this.savePhrase();
                }
            },
            unselectAllWords(fast = false, save = true) {
                if (save && this.selection.length == 1) {
                    this.saveWord();
                } else if (save && this.selectedPhrase !== -1) {
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
            unselectAllWordsProcess() {
                this.selectedPhrase = -1;
                this.selection = [];
                this.vocabBox.kanji = [];
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
            updateVocabBoxTranslationList() {
                this.vocabBox.translationList = this.vocabBox.translationText.split(';');
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
            saveWord(withStage = false, exampleSentenceChanged = false) {
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

                axios.post('/vocabulary/word/save', saveData).then((response) => {
                }).catch(function (error) {
                    console.log(error);
                }).then(() => {

                });

                if (exampleSentenceChanged) {
                    this.updateExampleSentence();
                }
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
                if (this.ankiAutoAddCards && stage < 0 && (this.vocabBox.selectedStageButton >= 0 || this.vocabBox.selectedStageButton === undefined)) {
                    this.addSelectedWordToAnki();
                }
                
                // save word/phrase
                this.updateSelectedWordStage();
                if (save == 'word') {
                    this.saveWord(true, stage < 0);

                } else if (save == 'phrase') {
                    this.savePhrase(true, stage < 0);
                }
            },
            updateSelectedWordStage() {
                if (this.selectedPhrase == -1 && this.selection.length) {
                    this.vocabBox.selectedStageButton = parseInt(this.textBlocks[this.selectedTextBlock].uniqueWords[this.selection[0].uniqueWordIndex].stage);
                } else if (this.selectedPhrase !== -1){
                    this.vocabBox.selectedStageButton = parseInt(this.textBlocks[this.selectedTextBlock].phrases[this.selectedPhrase].stage);
                }

                if (this.vocabBox.selectedStageButton == 2) {
                    this.vocabBox.selectedStageButton = undefined;
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
            makeSearchRequest() {
                this.vocabBox.searchResults = [];
                this.inflections = [];
                if (!this.selection.length || this.vocabBox.searchField == '') {
                    return;
                }

                axios.post('/dictionary/search', {
                    language: this.$props.language,
                    term: this.vocabBox.searchField
                }).then((response) => {
                    this.processVocabularySearchResults(response.data);
                });

                // search inflections
                // axios.post('/dictionary/search/inflections', {
                //     dictionary: 'jmdict',
                //     term: this.vocabBox.searchField
                // })
                // .then((response) => {
                //     this.processInflectionSearchResults(response.data);
                // });
            },
            processVocabularySearchResults(data) {
                this.vocabBox.searchResults = [];

                for (var dictionaryIndex = 0; dictionaryIndex < data.length; dictionaryIndex++) {
                    if (data[dictionaryIndex].name == 'JMDict') {
                        let searchResult = {
                            dictionary: data[dictionaryIndex].name,
                            color: data[dictionaryIndex].color,
                            records: []
                        };

                        for (var jmdictIndex = 0; jmdictIndex < data[dictionaryIndex].jmdictRecords.length; jmdictIndex++) {
                            var jmdictRecord = data[dictionaryIndex].jmdictRecords[jmdictIndex];
                            
                            searchResult.records.push({
                                word: jmdictRecord.words.length ? jmdictRecord.words[0] : '',
                                otherForms: data[dictionaryIndex].jmdictRecords[jmdictIndex].words,
                                definitions: data[dictionaryIndex].jmdictRecords[jmdictIndex].definitions,
                            });                            
                        }

                        this.vocabBox.searchResults.push(searchResult);
                    } else {
                        let searchResult = {
                            dictionary: data[dictionaryIndex].name,
                            color: data[dictionaryIndex].color,
                            records: []
                        };

                        for (var recordIndex = 0; recordIndex < data[dictionaryIndex].records.length; recordIndex++) {
                            searchResult.records.push({
                                word: data[dictionaryIndex].records[recordIndex].word,
                                definitions: data[dictionaryIndex].records[recordIndex].definitions,
                            });                            
                        }

                        this.vocabBox.searchResults.push(searchResult);
                    }
                }
            },
            processInflectionSearchResults(data) {
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
            },
            addDefinitionToInput(definition) {
                if (this.vocabBox.translationText.length && this.vocabBox.translationText[this.vocabBox.translationText.length - 1] !== ';') {
                    this.vocabBox.translationText += ';';
                }

                this.vocabBox.translationText += definition;
                this.updateVocabBoxTranslationList();
            },
            updateVocabBoxPosition() {
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

                this.vocabBox.position.top = selectedWordPositions.bottom - vocabBoxArea.top + 15;

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
            openVocabBoxEditPage() {
                this.makeSearchRequest();
                this.vocabBox.tab = 1;
                setTimeout(this.scrollToVocabBox, 120);
            },
            openKanji(kanji) {
                window.location.href = '/kanji/' + kanji;
            }
        }
    }
</script>
