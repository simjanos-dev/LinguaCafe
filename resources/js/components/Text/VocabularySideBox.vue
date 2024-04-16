<template>
    <v-card 
        id="vocab-side-box" 
        elevation="0"
        :class="{
            'new-phrase': $props.type === 'new-phrase', 
            'word-selected': $props.type === 'word',
            'phrase-selected': $props.type === 'phrase',
            'new-phrase-selected': $props.type === 'new-phrase',
            'pa-4': true,
            'rounded-l-0': true,
            'rounded-r-lg': true
        }" 
        :style="{
            'width': '400px',
            'border-left': '1px solid var(--v-gray2-base)',
            'left': $props.positionLeft + 'px',
            'top': $props.positionTop + 'px',
            'height': $props.height + 'px',
        }"
        @mouseup.stop=";"
    >
        <!-- Vocab box content -->
        <v-alert id="no-word-selected-title" border="top" color="gray" class="text--text" v-if="$props.type == 'empty'">
            Select a word or a phrase!
        </v-alert>

        <div class="pa-0 w-full" v-if="$props.type !== 'empty'">
            <!-- Word/phrase info -->
            <div class="vocab-box-subheader d-flex mb-2">
                <span id="vocab-side-box-title" v-if="$props.type == 'new-phrase'">New phrase</span>
                <span id="vocab-side-box-title" class="text-capitalize" v-else>{{ $props.type }}</span>
                <v-spacer />

                <!-- Options menu-->
                <v-menu left offset-y class="rounded-lg">
                    <template v-slot:activator="{ on, attrs }">
                        <v-btn dark icon v-bind="attrs" v-on="on" title="More options">
                            <v-icon>mdi-dots-horizontal</v-icon>
                        </v-btn>
                    </template>
                    <v-btn 
                        v-if="$props.type !== 'new-phrase'"
                        class="menu-button justify-start" 
                        @mouseup.stop="addSelectedWordToAnki"
                    >
                        <v-icon class="mr-1">mdi-cards</v-icon>Send to anki
                    </v-btn>
                </v-menu>
            </div>

            <!-- Word text fields -->
            <div class="d-flex" v-if="$props.type == 'word'">
                <v-text-field 
                    :class="{'mt-2': true, 'mb-2': ($props.language !== 'japanese' && $props.language !== 'chinese')}"
                    hide-details
                    placeholder="Lemma"
                    title="Lemma"
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
                    title="Word"
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
                    title="Lemma reading"
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
                    title="Reading"
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
                height="80"
                v-model="reading"
                @keyup="inputChanged"
                @keydown.stop=";"
            ></v-textarea>
            
            <!-- Stage buttons-->
            <template v-if="$props.type !== 'new-phrase'">
                <div id="vocab-box-stage-buttons" class="mb-2">
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
                        <v-icon small>mdi-check</v-icon>
                    </v-btn>
                    <v-btn 
                        :class="{'v-btn--active': stage == 1}" 
                        @click="setStage(1)" 
                        v-if="$props.type == 'word'"
                    >
                        <v-icon small>mdi-close</v-icon>
                    </v-btn>
                </div>
            </template>
            
            <!-- Translation -->
            <div class="vocab-box-subheader d-flex">
                Translation
            </div>
            <v-textarea
                class="mb-2 mt-1"
                placeholder="Translation"
                title="Translation"
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
        </div>

        <!-- Search box -->
        <vocabulary-search-box
            v-if="$props.type !== 'empty'"
            :deeplEnabled="$props.deeplEnabled"
            :language="$props.language"
            :searchTerm="searchField"
            @addDefinitionToInput="addDefinitionToInput"
        ></vocabulary-search-box>

        <div v-if="$props.type !== 'word'" class="d-flex mt-2 pl-0">
            <v-spacer />
            <v-btn 
                small
                rounded
                color="success"
                @click="addNewPhrase"
                v-if="$props.type == 'new-phrase'"
            >Save phrase</v-btn>
            <v-btn 
                small
                rounded
                color="error"
                @click="deletePhrase"
                v-if="$props.type == 'phrase'"
            >Delete phrase</v-btn>
        </div>
    </v-card>
</template>

<script>
    export default {
        props: {
            autoHighlightWords: Boolean,
            language: String,
            active: Boolean,
            type: String,
            word: String,
            phrase: Array,
            kanjiList: Array,
            stage: Number,
            deeplEnabled: Boolean,
            _reading: String,
            _baseWord: String,
            _baseWordReading: String,
            _phraseReading: String,
            _translationText: String,
            _searchField: String,
            positionLeft: Number,
            positionTop: Number,
            height: Number
        },
        data: function() {
            return {
                //temp, to be reviewed
                phraseCurrentlySaving: false,

                // data for word
                phraseText: '',
                reading: this.$props._reading,
                baseWord: this.$props._baseWord,
                baseWordReading: this.$props._baseWordReading,
                phraseReading: this.$props._phraseReading,

                // data for both
                translationText: this.$props._translationText,
                translationList: this.$props._translationText.split(';'),

                // ui data
                tab: 0,
                searchField: this.$props._searchField,
                searchResults: [],
            };
        },
        mounted: function() {
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
                this.$emit('deletePhrase');
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
