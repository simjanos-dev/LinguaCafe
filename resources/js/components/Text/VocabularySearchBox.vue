<template>
    <div id="vocabulary-search-box" class="border rounded-lg pa-2" :language="$props.language">
        <!-- Apis loading -->
         <div class="search-result disabled" v-if="dictionaryApiSearchLoading">
            <div class="search-result-title">
                <div class="dictionary-title-icon mr-1" style="background-color: var(--v-primary-base);">
                    <v-icon small>mdi-translate</v-icon>
                </div>
                {{ $props.searchTerm }}
                <div class="search-result-word default-font" :title="$props.searchTerm">API search</div>
            </div>
            <div class="search-result-definition rounded pr-2">
                loading <v-progress-circular indeterminate class="ml-1" size="20" width="3" color="primary"></v-progress-circular>
            </div>
        </div>

        <!-- API translations -->
        <div
            v-else
            class="search-result"
            v-for="(searchResult, searchResultIndex) in apiSearchResults"
        >

            <div class="search-result-title">
                <div class="dictionary-title-icon mr-1" :style="{'background-color': searchResult.dictionaryColor}">
                    <v-icon small>mdi-translate</v-icon>
                </div>
                {{ searchResult.dictionary }} <div class="search-result-word default-font" :title="$props.searchTerm">{{ $props.searchTerm }}</div>
            </div>
            
            <!-- Api search result -->
            <div 
                v-for="(definition, definitionIndex) in searchResult.definitions"
                :key="`api-search-result-${searchResultIndex}-${definitionIndex}`"
                class="search-result-definition rounded"
                @click="addDefinitionToInput(definition)"
            >
                {{ definition }} <v-icon small>mdi-plus</v-icon>
            </div>
        </div>

        <!-- Dictionary loading -->
        <div class="search-result disabled" v-if="dictionarySearchLoading">
            <div class="search-result-title">
                <div class="dictionary-title-icon mr-1" style="background-color: var(--v-primary-base);">
                    <v-icon small>mdi-list-box</v-icon>
                </div>
                <span class="default-font" :title="$props.searchTerm">{{ $props.searchTerm }}</span> <div class="search-result-word">Dictionary search</div>
            </div>

            <div class="search-result-definition rounded pr-2">
                loading <v-progress-circular indeterminate class="ml-1" size="20" width="3" color="primary"></v-progress-circular>
            </div>
        </div> 

        <!-- Dictionary no result message -->
        <div class="search-result disabled" v-if="!dictionarySearchLoading && !dictionarySearchResultsFound">
            <div class="search-result-title default-font" :title="$props.searchTerm">
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
                    <div class="search-result-title" :title="record.word">
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
                    <div class="search-result-title" :title="record.word">
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
            anyApiDictionaryEnabled: Boolean,
            searchTerm: String,
            exampleSentenceText: String
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
                dictionaryApiSearchLoading: false,
                dictionarySearchResultsFound: true,
                apiSearchResults: [],
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

                // api search
                if (this.$props.anyApiDictionaryEnabled) {
                    this.dictionaryApiSearchLoading = true;

                    axios.post('/dictionaries/api/search', {
                        language: this.$props.language,
                        term: this.$props.searchTerm,
                        context: this.$props.exampleSentenceText
                    }).then((response) => {
                        this.dictionaryApiSearchLoading = false;
                        this.apiSearchResults = response.data;
                    }).catch(() => {
                        this.dictionaryApiSearchLoading = false;
                        this.apiSearchResults = [];
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

                        console.log('wtf', data[dictionaryIndex])
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
