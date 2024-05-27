<template>
    <div id="vocabulary-search-box" class="border rounded-lg pa-2" :language="$props.language">
        <!-- DeepL translation -->
        <div 
            v-if="$props.deeplEnabled"
            :class="{
                'search-result': true, 
                'disabled': deeplSearchResults === 'DeepL error' || deeplSearchResults == 'loading'
            }"
        >
            <div class="search-result-title">
                <div class="dictionary-title-icon mr-1" style="background-color: #92B9E2">
                    <v-icon small>mdi-translate</v-icon>
                </div>
                DeepL <div class="search-result-word default-font" :title="$props.searchTerm">{{ $props.searchTerm }}</div>
            </div>
            
            <!-- DeepL search result -->
            <div 
                v-if="deeplSearchResults !== 'loading' && deeplSearchResults !== 'DeepL error' && deeplSearchResults.length"
                v-for="(definition, index) in deeplSearchResults"
                :key="'deepl-' + index"
                class="search-result-definition rounded"
                @click="addDefinitionToInput(definition)"
            >
                {{ definition }} <v-icon small>mdi-plus</v-icon>
            </div>

            <!-- DeepL search error -->
            <div 
                v-if="deeplSearchResults === 'DeepL error'"
                class="search-result-definition rounded"
            >
                {{ deeplSearchResults }}
            </div>

            <!-- DeepL loading -->
            <div v-if="deeplSearchResults == 'loading'" class="search-result-definition rounded pr-2">
                loading <v-progress-circular indeterminate class="ml-1" size="20" width="3" color="#92B9E2"></v-progress-circular>
            </div>
        </div>

        <!-- Dictionary loading -->
        <div class="search-result disabled" v-if="dictionarySearchLoading">
            <div class="search-result-title">
                <div class="dictionary-title-icon mr-1" style="background-color: var(--v-primary-base);">
                    <v-icon small>mdi-list-box</v-icon>
                </div>
                <span class="default-font">{{ $props.searchTerm }}</span> <div class="search-result-word">Dictionary search</div>
            </div>

            <div class="search-result-definition rounded pr-2">
                loading <v-progress-circular indeterminate class="ml-1" size="20" width="3" color="primary"></v-progress-circular>
            </div>
        </div> 

        <!-- Dictionary no result message -->
        <div class="search-result disabled" v-if="!dictionarySearchLoading && !dictionarySearchResultsFound">
            <div class="search-result-title default-font">
                <div class="dictionary-title-icon mr-1" style="background-color: var(--v-primary-base);">
                    <v-icon small>mdi-list-box</v-icon>
                </div>
                {{ $props.searchTerm }}
            </div>

            <div class="search-result-definition rounded pr-2">
                No dictionary results
            </div>
        </div> 
        
        <!-- Dictionary search results -->
        <div class="search-result jmdict" v-for="(searchResult, searchresultIndex) in searchResults" :key="searchresultIndex">
            <!-- Regular record -->
            <template v-if="searchResult.dictionary !== 'JMDict'">
                <div v-for="(record, recordIndex) in searchResult.records" :key="recordIndex">
                    <div class="search-result-title">
                        <div class="dictionary-title-icon mr-1"  :style="{'background-color': searchResult.color}">
                            <v-icon small>mdi-list-box</v-icon>
                        </div>
                        {{ searchResult.dictionary}}<div class="search-result-word" :title="record.word"> {{ record.word }} </div>
                    </div>

                    <div 
                        v-for="(definition, definitionIndex) in record.definitions" 
                        :key="definitionIndex" 
                        class="search-result-definition rounded"
                        @click="addDefinitionToInput(definition)"
                    >
                        {{ definition }} <v-icon small>mdi-plus</v-icon>
                    </div>
                </div>
            </template>

            <!-- JMDict record -->
            <template v-if="searchResult.dictionary == 'JMDict'">
                <div v-for="(record, recordIndex) in searchResult.records" :key="recordIndex">
                    <div class="search-result-title">
                        <div class="dictionary-title-icon mr-1"  :style="{'background-color': searchResult.color}">
                            <v-icon small>mdi-list-box</v-icon>
                        </div>
                        {{ searchResult.dictionary}}<div class="search-result-word default-font" :title="record.word"> {{ record.word }} </div>
                    </div>
                    
                    <div class="search-result-definition rounded" v-for="(definition, definitionIndex) in record.definitions" :key="definitionIndex" @click="addDefinitionToInput(definition)">
                        {{ definition }} <v-icon small>mdi-plus</v-icon>
                    </div>
                
                    <template v-if="record.otherForms.length">
                        <div class="vocab-box-subheader">Other forms:</div>
                        <div class="d-flex flex-wrap default-font">
                            <div v-for="(form, formIndex) in record.otherForms" :key="formIndex">
                                {{ form }}<span class="mr-2" v-if="formIndex < record.otherForms.length - 1">, </span>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            language: String,
            deeplEnabled: Boolean,
            searchTerm: String
        },
        watch: { 
            searchTerm: function(newVal, oldVal) {
                this.makeSearchRequest();
            }
        },
        data: function() {
            return {
                searchResults: [],
                dictionarySearchLoading: false,
                dictionarySearchResultsFound: true,
                deeplSearchResults: [],
            };
        },
        mounted: function() {
            this.makeSearchRequest();
        },
        methods: {
            addDefinitionToInput(definition) {
                this.$emit('addDefinitionToInput', definition);
            },
            makeSearchRequest() {
                this.searchResults = [];
                if (this.$props.searchTerm == '') {
                    return;
                }

                // dictionary search
                this.dictionarySearchLoading = true;
                this.dictionarySearchResultsFound = false;
                axios.post('/dictionaries/search', {
                    language: this.$props.language,
                    term: this.$props.searchTerm
                }).then((response) => {
                    this.processVocabularySearchResults(response.data);
                    this.dictionarySearchLoading = false;
                });

                // deepl search
                if (this.$props.deeplEnabled) {
                    this.deeplSearchResults = 'loading';
                    axios.post('/dictionaries/deepl/search', {
                        language: this.$props.language,
                        term: this.$props.searchTerm
                    }).then((response) => {
                        this.deeplSearchResults = response.data.definitions.split(';');

                    }).catch(() => {
                        this.deeplSearchResults = 'DeepL error';
                    });
                }
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

                        if (searchResult.records.length) {
                            this.dictionarySearchResultsFound = true;
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

                        if (searchResult.records.length) {
                            this.dictionarySearchResultsFound = true;
                        }
                        
                        this.searchResults.push(searchResult);
                    }
                }
            }
        }
    }
</script>
