<template>
    <v-card 
        id="vocab-box" 
        :class="{
            'new-phrase': $props.type === 'new-phrase', 
            'rounded-lg': true,
            'd-flex': true
        }" 
        :style="{
            'top': positionTop + 'px', 
            'left': positionLeft + 'px',
            'width': $props.width + 'px'
        }"
        @mouseup.stop=";"
    >
        <v-overlay 
            v-if="active && phraseCurrentlySaving"
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
            <v-tabs-items v-model="tab">
                <!-- Word info page -->
                <v-tab-item :value="0">
                    <v-card-text class="pa-0">
                        <!-- Single word -->
                        <template v-if="$props.type === 'word'">
                            <div class="vocab-box-subheader mb-2 mt-0"><span class="rounded-pill py-1 px-3">Word</span></div>
                            <!-- With base word -->
                            <div class="expression mb-2 text-center" v-if="baseWord !== ''">
                                <ruby>
                                    {{ baseWord }}
                                    <rt v-if="($props.language == 'japanese' || $props.language == 'chinese')">
                                        {{ baseWordReading }}
                                    </rt>
                                </ruby>
                                <v-icon color="text">mdi-arrow-right-thick</v-icon>
                                <ruby>
                                    {{ word }}
                                    <rt v-if="($props.language == 'japanese' || $props.language == 'chinese')">
                                        {{ reading}}
                                    </rt>
                                </ruby>
                            </div>
                            
                            <!-- No base word -->
                            <div 
                                class="expression mb-2 text-center" 
                                v-if="baseWord == ''"
                            >
                                <ruby>
                                    {{ word }}
                                    <rt v-if="($props.language == 'japanese' || $props.language == 'chinese')">
                                        {{ reading }}
                                    </rt>
                                </ruby>
                            </div>
                        </template>

                        <!-- Phrase -->
                        <template v-if="$props.type !== 'word'">
                            <div class="vocab-box-subheader mb-2 mt-0"><span class="rounded-pill py-1 px-3">Phrase</span></div>
                            <!-- Phrase text -->
                            <div class="expression mb-2">
                                <template v-for="(word, index) in phrase" v-if="word.word !== 'NEWLINE'">
                                    <span :class="{'mr-2': word.spaceAfter}">{{ word.word }}</span>
                                </template>
                            </div>

                            <!-- Phrase reading -->
                            <template v-if="($props.language == 'japanese' || $props.language == 'chinese')">
                                <div class="vocab-box-subheader mb-2 mt-4"><span class="rounded-pill py-1 px-3">Reading</span></div>
                                <div class="expression mb-2 mt-4">{{ reading }}</div>
                            </template>
                        </template>
                        
                        <!-- Kanji list -->
                        <!--
                        <template v-if="$props.kanjiList.length && $props.language == 'japanese'">
                            <div class="vocab-box-subheader mb-2 mt-4"><span class="rounded-pill py-1 px-3">Kanji</span></div>
                            <div id="vocab-box-kanji-box" class="d-flex flex-wrap ma-0 mb-2">
                                <div 
                                    class="kanji rounded-lg mr-2" 
                                    v-for="(kanji, index) in $props.kanjiList" 
                                    :key="index"
                                    @click="openKanji(kanji)"
                                >
                                    {{ kanji }}
                                </div>
                            </div>
                        </template>
                        -->

                        <!-- Definitions -->
                        <!--
                        <template v-if="translationList.length">
                            <div class="vocab-box-subheader mb-2 mt-4"><span class="rounded-pill py-1 px-3">Definitions</span></div>
                            <ul id="definitions" class="ma-0">
                                <li v-for="(translation, index) in translationList" :key="index">{{ translation }}</li>
                            </ul>
                        </template>
                        -->

                        <!-- Stage buttons-->
                        <template v-if="type !== 'new-phrase'">
                            <div class="vocab-box-subheader d-flex mb-2 mt-4">
                                <span class="rounded-pill py-1 px-3">Level</span>
                                <v-spacer />

                                <!-- Level info box -->
                                <v-menu offset-y left nudge-top="-12px">
                                    <template v-slot:activator="{ on, attrs }">
                                        <div>
                                            <v-icon class="mr-2" v-bind="attrs" v-on="on">mdi-help-circle-outline</v-icon>
                                        </div>
                                    </template>
                                    <v-card outlined class="rounded-lg pa-4" width="320px">
                                        A word's or phrase's level represents how well you know it. 
                                        The closer it is to 0, the closer you are to learn it, and it 
                                        will appear in reviews less frequently.<br><br>

                                        <v-icon class="mr-2">mdi-check</v-icon>
                                        represents known words.<br>
                                        <v-icon class="mr-2">mdi-close</v-icon>
                                        represents ignored words. Ignored words do not count in learned word statistics.
                                    </v-card>
                                </v-menu>
                            </div>
                            <div id="vocab-box-stage-buttons" class="mb-4">
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
                                    v-if="type == 'word'"
                                >
                                    <v-icon>mdi-close</v-icon>
                                </v-btn>
                            </div>
                        </template>
                      
                        <!-- Translation -->
                        <v-textarea
                            :class="{'mt-2': $props.language !== 'japanese' && $props.language !== 'chinese'}"
                            label="Translation"
                            filled
                            dense
                            no-resize
                            rounded
                            hide-details
                            height="100"
                            v-model="translationText"
                            @change="inputChanged"
                        ></v-textarea>

                        <!-- Search term -->
                        <v-text-field 
                            label="Dictionary search"
                            class="mt-2 mb-3"
                            filled
                            dense
                            rounded
                            width="100%"
                            hide-details
                            v-model="searchField"
                            @change="makeSearchRequest"
                        ></v-text-field>

                        <!-- Search results -->
                        <div id="search-results" class="border rounded-lg">
                            <div class="search-result jmdict" v-for="(searchResult, searchresultIndex) in searchResults" :key="searchresultIndex">
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

                    <v-card-actions v-if="type !== 'word'" class="mt-2 pl-0">
                        <v-spacer />
                        <v-btn 
                            small
                            rounded
                            color="success"
                            @click="addNewPhrase"
                            v-if="type == 'new-phrase'"
                        >Save phrase</v-btn>
                        <v-btn 
                            small
                            rounded
                            color="error"
                            @click="deletePhrase"
                            v-if="type == 'phrase'"
                        >Delete phrase</v-btn>
                    </v-card-actions>
                </v-tab-item>

                <!-- Editing page -->
                <v-tab-item :value="1">
                    <v-card-text id="vocab-box-edit-page" class="pa-0">
                        <!-- Word text fields -->
                        <div class="d-flex" v-if="type == 'word'">
                            <v-text-field 
                                :class="{'mt-2': true, 'mb-2': ($props.language !== 'japanese' && $props.language !== 'chinese')}"
                                hide-details
                                label="Lemma"
                                filled
                                dense
                                rounded
                                v-model="baseWord"
                                @change="inputChanged"
                            ></v-text-field>
                            <v-text-field 
                                :class="{'mt-2': true, 'mb-2': ($props.language !== 'japanese' && $props.language !== 'chinese')}"
                                hide-details
                                label="Word"
                                disabled
                                filled
                                dense
                                rounded
                                :value="word"
                                @change="inputChanged"
                            ></v-text-field>
                        </div>

                        <!-- Reading fields -->
                        <div class="d-flex" v-if="type == 'word' && ($props.language == 'japanese' || $props.language == 'chinese')">
                            <v-text-field 
                                class="my-2"
                                hide-details
                                label="Lemma reading"
                                filled
                                dense
                                rounded
                                v-model="baseWordReading"
                                @change="inputChanged"
                            ></v-text-field>
                            <v-text-field 
                                class="my-2"
                                hide-details
                                label="Reading"
                                filled
                                dense
                                rounded
                                v-model="reading"
                                @change="inputChanged"
                            ></v-text-field>
                        </div>

                        <!-- Phrase fields -->
                        <v-textarea
                            v-if="type !== 'word' && ($props.language == 'japanese' || $props.language == 'chinese')"
                            class="my-2"
                            label="Reading"
                            filled
                            dense
                            no-resize
                            rounded
                            hide-details
                            height="100"
                            v-model="reading"
                            @change="inputChanged"
                        ></v-textarea>
                    </v-card-text>
                </v-tab-item>
            </v-tabs-items>
        </div>

        <!-- Vocab box toolbar -->
        <div class="vocab-box-toolbar d-flex flex-column align-center flex-wrap pt-1 rounded-r-lg">
            <v-btn dark icon @click="close" title="Close"><v-icon>mdi-close</v-icon></v-btn>
            <v-btn dark icon @click="tab = 1;" title="Edit" v-if="tab == 0"><v-icon>mdi-pencil</v-icon></v-btn>
            <v-btn dark icon @click="tab = 0;" v-if="tab == 1" title="Back"><v-icon>mdi-arrow-left</v-icon></v-btn>
            <v-menu left offset-y class="rounded-lg">
                <template v-slot:activator="{ on, attrs }">
                    <v-btn dark icon v-bind="attrs" v-on="on" title="More options">
                        <v-icon>mdi-dots-horizontal</v-icon>
                    </v-btn>
                </template>
                <v-btn 
                    v-if="type !== 'new-phrase'"
                    class="menu-button justify-start" 
                    @click="addSelectedWordToAnki"
                >
                    <v-icon class="mr-1">mdi-cards</v-icon>Send to anki
                </v-btn>
            </v-menu>
        </div>
    </v-card>
</template>

<script>
    export default {
        props: {
            language: String,
            active: Boolean,
            type: String,
            word: String,
            phrase: Array,
            kanjiList: Array,
            stage: Number,
            _reading: String,
            _baseWord: String,
            _baseWordReading: String,
            _phraseReading: String,
            _translationText: String,
            _searchField: String,
            positionLeft: Number,
            positionTop: Number,
            width: Number
        },
        data: function() {
            return {
                //temp, to be reviewed
                phraseCurrentlySaving: false,

                // data for word
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
            this.makeSearchRequest();
        },
        methods: {
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
            makeSearchRequest() {
                this.searchResults = [];
                if (this.searchField == '') {
                    return;
                }

                axios.post('/dictionary/search', {
                    language: this.$props.language,
                    term: this.searchField
                }).then((response) => {
                    this.processVocabularySearchResults(response.data);
                });

                // search inflections
                // axios.post('/dictionary/search/inflections', {
                //     dictionary: 'jmdict',
                //     term: this.searchField
                // })
                // .then((response) => {
                //     this.processInflectionSearchResults(response.data);
                // });
            },
            processVocabularySearchResults(data) {
                this.searchResults = [];

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

                        this.searchResults.push(searchResult);
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

                        this.searchResults.push(searchResult);
                    }
                }
            },
            
            addDefinitionToInput(definition) {
                if (this.translationText.length && this.translationText[this.translationText.length - 1] !== ';') {
                    this.translationText += ';';
                }

                this.translationText += definition;
                this.inputChanged();
            },
            inputChanged() {
                this.updateVocabBoxTranslationList();

                this.$emit('updateVocabBoxData', {
                    reading: this.reading,
                    baseWord: this.baseWord,
                    baseWordReading: this.baseWordReading,
                    phraseReading: this.phraseReading,
                    translationText: this.translationText
                });
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
