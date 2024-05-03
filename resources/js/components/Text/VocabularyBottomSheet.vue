<template>
    <v-card id="vocab-bottom-sheet" color="foreground" @mouseup.stop=";">
        <!-- Content -->
        <div class="px-3">
            <v-tabs-items v-model="tab">
                <!-- Word info page -->
                <v-tab-item :value="0">
                    <!-- Word text fields -->
                    <div class="d-flex" v-if="$props.type == 'word'">
                        <v-text-field 
                            :class="{'mt-2': true, 'mb-2': ($props.language !== 'japanese' && $props.language !== 'chinese')}"
                            hide-details
                            placeholder="Lemma"
                            filled
                            dense
                            rounded
                            v-model="baseWord"
                            @keyup="inputChanged"
                            @keydown.stop=";"
                        ></v-text-field>
                        <v-icon class="mt-1 mx-1">mdi-arrow-right</v-icon>
                        <v-text-field 
                            :class="{'mt-2': true, 'mb-2': ($props.language !== 'japanese' && $props.language !== 'chinese')}"
                            hide-details
                            placeholder="Word"
                            disabled
                            filled
                            dense
                            rounded
                            :value="word"
                            @keyup="inputChanged"
                            @keydown.stop=";"
                        ></v-text-field>
                    </div>

                    <!-- Reading fields -->
                    <div class="d-flex" v-if="$props.type == 'word' && ($props.language == 'japanese' || $props.language == 'chinese')">
                        <v-text-field 
                            class="my-2"
                            hide-details
                            placeholder="Lemma reading"
                            filled
                            dense
                            rounded
                            v-model="baseWordReading"
                            @keyup="inputChanged"
                            @keydown.stop=";"
                        ></v-text-field>
                        <v-icon class="mt-1 mx-1">mdi-arrow-right</v-icon>
                        <v-text-field 
                            class="my-2"
                            hide-details
                            placeholder="Reading"
                            filled
                            dense
                            rounded
                            v-model="reading"
                            @keyup="inputChanged"
                            @keydown.stop=";"
                        ></v-text-field>
                    </div>

                    <!-- Phrase text -->
                    <v-textarea
                        v-if="$props.type !== 'word'"
                        class="my-2"
                        label="Phrase"
                        filled
                        dense
                        no-resize
                        rounded
                        hide-details
                        height="80"
                        disabled
                        :value="phraseText"
                        @keydown.stop=";"
                    ></v-textarea>

                    <!-- Phrase reading -->
                    <v-textarea
                        v-if="$props.type !== 'word' && ($props.language == 'japanese' || $props.language == 'chinese')"
                        class="my-2"
                        label="Reading"
                        filled
                        dense
                        no-resize
                        rounded
                        hide-details
                        height="100"
                        v-model="reading"
                        @keyup="inputChanged"
                        @keydown.stop=";"
                    ></v-textarea>

                    <!-- Translation -->
                    <v-textarea
                        :class="{'mt-2': $props.language !== 'japanese' && $props.language !== 'chinese'}"
                        placeholder="Translation"
                        filled
                        dense
                        no-resize
                        rounded
                        hide-details
                        height="100"
                        v-model="translationText"
                        @keyup="inputChanged('translation')"
                        @keydown.stop=";"
                    ></v-textarea>

                    <!-- Search field -->
                    <v-text-field 
                        placeholder="Dictionary search"
                        class="dictionary-search-field mt-2 mb-3"
                        width="100%"
                        prepend-inner-icon="mdi-magnify"
                        filled
                        dense
                        rounded
                        hide-details
                        :value="searchField"
                        @change="searchFieldChanged"
                        @keydown.stop=";"
                    ></v-text-field>
                </v-tab-item>

                <v-tab-item :value="1"></v-tab-item>
            </v-tabs-items>
        </div>

        <v-card-text class="searchBoxBorder mx-auto pa-3 rounded-lg border">
            <v-tabs-items v-model="tab">
                <!-- Main tab -->
                <v-tab-item :value="0">
                    <!-- Edit fields -->
                    <v-card-text id="vocab-box-edit-page" class="pa-0">
                        <!-- Search box -->
                        <vocabulary-search-box
                            v-if="$props.type !== 'empty'"
                            :deeplEnabled="$props.deeplEnabled"
                            :language="$props.language"
                            :searchTerm="searchField"
                            @addDefinitionToInput="addDefinitionToInput"
                        ></vocabulary-search-box>
                    </v-card-text>
                </v-tab-item>

                <!-- Inflections tab -->
                <v-tab-item :value="1">
                    <v-simple-table
                        v-if="$props.inflections.length"
                        class="border rounded-lg no-hover mx-auto" 
                    >
                        <thead>
                            <tr>
                                <th class="text-center">Form</th>
                                <th class="text-center">Affirmative</th>
                                <th class="text-center">Negative</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(inflection, index) in $props.inflections" :key="index">
                                <td class="px-2">{{ inflection.name }}</td>
                                <td class="px-1 text-center">{{ inflection.affPlain }}</td>
                                <td class="px-1 text-center">{{ inflection.negPlain }}</td>
                            </tr>
                        </tbody>
                    </v-simple-table>
                </v-tab-item>

            </v-tabs-items>
        </v-card-text>
        
        <!-- Action buttons -->
        <v-card-actions class="d-flex flex-column">
            <!-- Stage buttons-->
            <template v-if="$props.type !== 'new-phrase'">
                <div id="vocabulary-bottom-sheet-stage-buttons" class="mb-1">
                    <v-btn :class="{'v-btn--active': stage == -7}" @click="setStage(-7)">7</v-btn>
                    <v-btn :class="{'v-btn--active': stage == -6}" @click="setStage(-6)">6</v-btn>
                    <v-btn :class="{'v-btn--active': stage == -5}" @click="setStage(-5)">5</v-btn>
                    <v-btn :class="{'v-btn--active': stage == -4}" @click="setStage(-4)">4</v-btn>
                    <v-btn :class="{'v-btn--active': stage == -3}" @click="setStage(-3)">3</v-btn>
                    <v-btn :class="{'v-btn--active': stage == -2}" @click="setStage(-2)">2</v-btn>
                    <v-btn :class="{'v-btn--active': stage == -1}" @click="setStage(-1)">1</v-btn>
                    <v-btn 
                        :class="{'v-btn--active': stage == 0}"
                        @click="setStage(0)" 
                    >
                        <v-icon>mdi-check</v-icon>
                    </v-btn>
                    <v-btn 
                        :class="{'v-btn--active': stage == 1}" 
                        @click="setStage(1)" 
                        v-if="$props.type == 'word'"
                    >
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </div>
            </template>
            
            <div class="w-100 d-flex justify-space-around mb-2">
                <!-- Main tab -->
                <template v-if="tab === 0">
                    <v-btn icon @click="addSelectedWordToAnki"><v-icon>mdi-cards</v-icon></v-btn>
                    <v-btn icon @click="textToSpeech"><v-icon>mdi-bullhorn</v-icon></v-btn>
                    <v-btn icon :disabled="!$props.inflections.length" @click="tab = 1;"><v-icon>mdi-list-box</v-icon></v-btn>
                </template>

                <!-- Back arrow on other tabs -->
                <template v-else>
                    <v-btn disabled icon><v-icon>mdi-cards</v-icon></v-btn>
                    <v-btn disabled icon><v-icon>mdi-bullhorn</v-icon></v-btn>
                    <v-btn icon @click="tab = 0;" title="Back"><v-icon>mdi-arrow-left</v-icon></v-btn>
                </template>
            </div>

            <!-- Save phrase button -->
            <v-btn 
                class="w-100 mx-0"
                height="42px"
                small
                rounded
                depressed
                color="success"
                @click="addNewPhrase"
                v-if="$props.type == 'new-phrase'"
            >Save phrase</v-btn>

            <!-- Save phrase button -->
            <v-btn 
                class="w-100 mx-0"
                height="42px"
                rounded
                depressed
                color="error"
                @click="deletePhrase"
                v-if="$props.type == 'phrase'"
            >Delete phrase</v-btn>
            
            <!-- Close button -->
            <v-btn 
                class="w-100 mx-0 mt-2"
                height="42px"
                color="primary" 
                rounded 
                depressed 
                @click="unselectAllWords()"
            >Close</v-btn>
        </v-card-actions>
    </v-card>
</template>


<script>
export default {
    data: function() {
        return {
            // data for word
            reading: this.$props._reading,
            baseWord: this.$props._baseWord,
            baseWordReading: this.$props._baseWordReading,
            phraseReading: this.$props._phraseReading,
            phraseText: '',

            // data for both
            translationText: this.$props._translationText,
            translationList: this.$props._translationText.split(';'),

            // ui data
            tab: 0,
            searchField: this.$props._searchField,
            searchResults: [],
        }
    },
    props: {
        autoHighlightWords: Boolean,
        language: String,
        active: Boolean,
        type: String,
        word: String,
        phrase: Array,
        kanjiList: Array,
        stage: Number,
        inflections: Array,
        deeplEnabled: Boolean,
        textToSpeechAvailable: Boolean,
        _reading: String,
        _baseWord: String,
        _baseWordReading: String,
        _phraseReading: String,
        _translationText: String,
        _searchField: String,
    },
    mounted() {
        // generate phrase text
        for (let wordIndex = 0; wordIndex < this.$props.phrase.length; wordIndex++) {
            if (this.$props.phrase.word === 'NEWLINE') {
                continue;
            }
            
            this.phraseText += this.$props.phrase[wordIndex].word;

            if (this.$props.phrase[wordIndex].spaceAfter) {
                this.phraseText += ' ';
            }
        }
    },
    methods: {
            textToSpeech() {
                this.$emit('textToSpeech');
            },
            searchFieldChanged(event) {
                if (event === '') {
                    return;
                }
                
                this.searchField = event;
            },
            setStage(stage) {
                this.$emit('setStage', stage);
            },
            openKanji(kanji) {
                window.location.href = '/kanji/' + kanji;
            },
            addNewPhrase() {
                this.$emit('addNewPhrase');
            },
            deletePhrase() {
                this.$emit('showDeletePhraseDialog');
            },
            updateVocabBoxTranslationList() {
                this.translationList = this.$props._translationText.split(';');
            },
            addDefinitionToInput(definition) {
                if (this.translationText.length && this.translationText[this.translationText.length - 1] !== ';') {
                    this.translationText += ';';
                }

                this.translationText += definition;
                this.inputChanged('translation');
            },
            inputChanged(inputName = '') {
                this.updateVocabBoxTranslationList();

                this.$emit('updateVocabBoxData', {
                    reading: this.reading,
                    baseWord: this.baseWord,
                    baseWordReading: this.baseWordReading,
                    phraseReading: this.phraseReading,
                    translationText: this.translationText
                });

                if (inputName == 'translation' && this.$props.stage >= 0 && this.$props.autoHighlightWords && this.translationText !== '') {
                    this.setStage(-7);
                }
            },
            unselectAllWords() {
                this.$emit('unselectAllWords');
            },
            addSelectedWordToAnki() {
                this.$emit('addSelectedWordToAnki');
            },
            close() {
                this.$emit('unselectAllWords');
            }
    }
}
</script>