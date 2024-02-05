<template>
    <div id="vocabulary-search-box">
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
            @keydown.stop=";"
        ></v-text-field>


        <!-- Search results -->
        <div id="search-results" class="border rounded-lg">

            <!-- DeepL translation -->
            <div :class="{'search-result': true, 'disabled': deeplSearchResult.length || deeplSearchResult === 'DeepL error' || deeplSearchResult == 'loading'}">
                <div class="search-result-title rounded px-2" style="background-color: #92B9E2">
                    {{ searchField }} <div class="dictionary">DeepL</div>
                </div>
                
                <!-- DeepL search result -->
                <div 
                    v-if="deeplSearchResult !== 'loading' && deeplSearchResult !== 'DeepL error'"
                    class="search-result-definition rounded"
                    @click="addDefinitionToInput(deeplSearchResult)"
                >
                    {{ deeplSearchResult }} <v-icon v-if="deeplSearchResult.length">mdi-plus</v-icon>
                </div>

                <!-- DeepL search error -->
                <div 
                    v-if="deeplSearchResult === 'DeepL error'"
                    class="search-result-definition rounded"
                >
                    {{ deeplSearchResult }}
                </div>

                <!-- DeepL loading -->
                <div v-if="deeplSearchResult == 'loading'" class="search-result-definition rounded pr-2">
                    loading <v-progress-circular indeterminate class="ml-1" size="20" width="3" color="#92B9E2"></v-progress-circular>
                </div>
            </div>

            <!-- Dictionary loading -->
            <div class="search-result disabled" v-if="dictionarySearchLoading">
                <div class="search-result-title rounded px-2" style="background-color: #C5947D;">
                    {{ searchField }} <div class="dictionary">Dictionary search</div>
                </div>

                <div class="search-result-definition rounded pr-2">
                    loading <v-progress-circular indeterminate class="ml-1" size="20" width="3" color="primary"></v-progress-circular>
                </div>
            </div> 

            <!-- Dictionary no result message -->
            <div class="search-result disabled" v-if="!dictionarySearchLoading && !dictionarySearchResultsFound">
                <div class="search-result-title rounded px-2" style="background-color: #C5947D;">
                    {{ searchField }} <div class="dictionary">Dictionary search</div>
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
    </div>
</template>

<script>
    export default {
        props: {
            language: String,
            _searchTerm: String
        },
        data: function() {
            return {
                searchField: this.$props._searchTerm,
                searchResults: [],
                dictionarySearchLoading: false,
                dictionarySearchResultsFound: true,
                deeplSearchResult: '',
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
                if (this.searchField == '') {
                    return;
                }

                // dictionary search
                this.dictionarySearchLoading = true;
                this.dictionarySearchResultsFound = false;
                axios.post('/dictionary/search', {
                    language: this.$props.language,
                    term: this.searchField
                }).then((response) => {
                    this.processVocabularySearchResults(response.data);
                    this.dictionarySearchLoading = false;
                });

                // deepl search
                this.deeplSearchResult = 'loading';
                axios.post('/dictionaries/deepl/search', {
                    language: this.$props.language,
                    term: this.searchField
                }).then((response) => {
                    this.deeplSearchResult = response.data.definition;
                }).catch(() => {
                    this.deeplSearchResult = 'DeepL error';
                });
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
